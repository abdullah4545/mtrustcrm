<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\Organization;
use App\Models\OrganizationCategory;
use App\Models\OrganizationType;
use App\Models\Upazila;
use App\Services\SimpleXlsxToCsv;
use App\Support\CrmAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class OrganizationImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:org.create');
    }

    public function upload(Request $request, SimpleXlsxToCsv $converter)
    {
        abort_if(CrmAccess::isStaff(), 403, 'Bulk organization import is not available for staff users.');

        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:20480',
        ]);

        $dir = storage_path('app/organization-imports');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $token = Str::random(48);
        $xlsxPath = $dir.DIRECTORY_SEPARATOR.$token.'.xlsx';
        $csvPath  = $dir.DIRECTORY_SEPARATOR.$token.'.csv';
        $request->file('file')->move($dir, $token.'.xlsx');

        try {
            $meta = $converter->convert($xlsxPath, $csvPath);
            $headerMap = $this->buildHeaderMap($meta['headers']);
            $this->validateRequiredHeaders($headerMap);
        } catch (Throwable $e) {
            @unlink($xlsxPath);
            @unlink($csvPath);
            return response()->json([
                'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Unable to read the XLSX file.',
            ], 422);
        }

        session()->put('organization_imports.'.$token, [
            'csv' => $csvPath,
            'xlsx' => $xlsxPath,
            'total_rows' => (int) $meta['total_rows'],
            'header_map' => $headerMap,
            'created_at' => now()->timestamp,
        ]);

        return response()->json([
            'message' => 'File ready for import.',
            'token' => $token,
            'total_rows' => (int) $meta['total_rows'],
            'chunk_size' => 500,
        ]);
    }

    public function process(Request $request)
    {
        abort_if(CrmAccess::isStaff(), 403, 'Bulk organization import is not available for staff users.');

        $data = $request->validate([
            'token' => 'required|string|max:80',
            'byte_offset' => 'nullable|integer|min:0',
            'processed' => 'nullable|integer|min:0',
        ]);

        $token = $data['token'];
        $meta = session('organization_imports.'.$token);
        if (!$meta || empty($meta['csv']) || !is_file($meta['csv'])) {
            return response()->json(['message' => 'Import session expired. Please upload the file again.'], 422);
        }

        $handle = fopen($meta['csv'], 'rb');
        if (!$handle) {
            return response()->json(['message' => 'Unable to open import working file.'], 422);
        }

        $offset = (int) ($data['byte_offset'] ?? 0);
        $processedBefore = (int) ($data['processed'] ?? 0);

        if ($offset > 0) {
            fseek($handle, $offset);
        } else {
            // skip CSV header
            fgetcsv($handle);
        }

        $lookups = $this->lookupMaps();
        $headerMap = $meta['header_map'];
        $chunkSize = 500;
        $stats = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $errors = [];
        $read = 0;

        while ($read < $chunkSize && ($row = fgetcsv($handle)) !== false) {
            $read++;
            try {
                $result = $this->importRow($row, $headerMap, $lookups);
                $stats[$result]++;
            } catch (Throwable $e) {
                $stats['failed']++;
                if (count($errors) < 15) {
                    $name = $this->value($row, $headerMap, 'name');
                    $errors[] = ($name ?: 'Unknown organization').': '.$e->getMessage();
                }
            }
        }

        $nextOffset = ftell($handle);
        $done = feof($handle);
        fclose($handle);

        $processed = min((int) $meta['total_rows'], $processedBefore + $read);

        if ($done || $processed >= (int) $meta['total_rows']) {
            @unlink($meta['csv']);
            @unlink($meta['xlsx']);
            session()->forget('organization_imports.'.$token);
            $done = true;
        }

        return response()->json([
            'done' => $done,
            'byte_offset' => $nextOffset,
            'processed' => $processed,
            'total_rows' => (int) $meta['total_rows'],
            'stats' => $stats,
            'errors' => $errors,
        ]);
    }

    private function importRow(array $row, array $headers, array $maps): string
    {
        $name = trim($this->value($row, $headers, 'name'));
        if ($name === '') {
            return 'skipped';
        }

        $divisionName = $this->value($row, $headers, 'division');
        $districtName = $this->value($row, $headers, 'district');
        $upazilaName  = $this->value($row, $headers, 'upazila');

        $divisionKey = $this->norm($this->divisionAlias($divisionName));
        $divisionId = $maps['divisions'][$divisionKey] ?? null;

        $districtKey = $this->norm($this->districtAlias($districtName));
        $districtId = $divisionId ? ($maps['districts'][$divisionId.'|'.$districtKey] ?? null) : null;

        $upazilaKey = $this->norm($this->upazilaAlias($upazilaName));
        $upazilaId  = $districtId && $upazilaKey !== ''
            ? ($maps['upazilas'][$districtId.'|'.$upazilaKey] ?? null)
            : null;

        $categoryName = $this->normalizeCategory($this->value($row, $headers, 'category'));
        $typeName = $this->normalizeType($this->value($row, $headers, 'type'));

        $categoryId = $maps['categories'][$this->norm($categoryName)] ?? null;
        $typeId = $maps['types'][$this->norm($typeName)] ?? null;

        if (!$categoryId && $categoryName === 'Blood Bank') {
            $categoryId = $maps['categories'][$this->norm('Others')] ?? null;
        }

        $dghsId = trim($this->value($row, $headers, 'dghs_facility_id'));
        $payload = [
            'organization_category_id' => $categoryId,
            'organization_type_id' => $typeId,
            'name' => $name,
            'address' => trim($this->value($row, $headers, 'address')),
            'division_id' => $divisionId,
            'district_id' => $districtId,
            'upazila_id' => $upazilaId,
            'phone_primary' => trim($this->value($row, $headers, 'phone')),
            'phone_secondary' => trim($this->value($row, $headers, 'secondary_phone')),
            'email' => trim($this->value($row, $headers, 'email')),
            'website' => trim($this->value($row, $headers, 'website')),
            'map_location_link' => trim($this->value($row, $headers, 'map_location')),
            'dghs_facility_id' => $dghsId !== '' ? $dghsId : null,
        ];

        // Never erase existing verified/manual data with blank spreadsheet cells.
        $nonBlank = array_filter($payload, fn ($v) => $v !== null && $v !== '');
        $nonBlank['name'] = $name;

        $organization = null;
        if ($dghsId !== '') {
            $organization = Organization::where('dghs_facility_id', $dghsId)->first();
        }

        if (!$organization) {
            $organization = Organization::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
                ->when($districtId, fn ($q) => $q->where('district_id', $districtId))
                ->when($upazilaId, fn ($q) => $q->where('upazila_id', $upazilaId))
                ->first();
        }

        if ($organization) {
            $organization->fill($nonBlank);
            $organization->save();
            return 'updated';
        }

        $nonBlank['status'] = 'active';
        $nonBlank['created_by'] = auth()->id();
        Organization::create($nonBlank);
        return 'imported';
    }

    private function lookupMaps(): array
    {
        $divisions = [];
        foreach (Division::select('id','name')->get() as $item) {
            $divisions[$this->norm($item->name)] = $item->id;
        }

        $districts = [];
        foreach (District::select('id','division_id','name')->get() as $item) {
            $districts[$item->division_id.'|'.$this->norm($item->name)] = $item->id;
        }

        $upazilas = [];
        foreach (Upazila::select('id','district_id','name')->get() as $item) {
            $upazilas[$item->district_id.'|'.$this->norm($item->name)] = $item->id;
        }

        $categories = [];
        foreach (OrganizationCategory::select('id','name')->get() as $item) {
            $categories[$this->norm($item->name)] = $item->id;
        }

        $types = [];
        foreach (OrganizationType::select('id','name')->get() as $item) {
            $types[$this->norm($item->name)] = $item->id;
        }

        return compact('divisions','districts','upazilas','categories','types');
    }

    private function buildHeaderMap(array $headers): array
    {
        $aliases = [
            'category' => ['category'],
            'type' => ['type','ownership'],
            'name' => ['organization name','facility name','name'],
            'address' => ['address'],
            'division' => ['division'],
            'district' => ['district'],
            'upazila' => ['upazila'],
            'phone' => ['phone','phone primary','primary phone','mobile','mobile no'],
            'secondary_phone' => ['secondary phone','phone secondary','phone 2'],
            'email' => ['email'],
            'website' => ['website'],
            'map_location' => ['map location','map location link','google map'],
            'dghs_facility_id' => ['dghs facility id','facility id'],
        ];

        $normalized = [];
        foreach ($headers as $index => $header) {
            $normalized[$this->headerNorm($header)] = $index;
        }

        $map = [];
        foreach ($aliases as $key => $names) {
            $map[$key] = null;
            foreach ($names as $name) {
                $n = $this->headerNorm($name);
                if (array_key_exists($n, $normalized)) {
                    $map[$key] = $normalized[$n];
                    break;
                }
            }
        }

        return $map;
    }

    private function validateRequiredHeaders(array $map): void
    {
        if ($map['name'] === null) {
            throw new RuntimeException('Organization Name / Facility Name column was not found.');
        }
        if ($map['division'] === null || $map['district'] === null) {
            throw new RuntimeException('Division and District columns are required for safe location matching.');
        }
    }

    private function value(array $row, array $headers, string $key): string
    {
        $index = $headers[$key] ?? null;
        if ($index === null) {
            return '';
        }
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    private function normalizeCategory(string $value): string
    {
        $value = trim($value);
        $n = $this->norm($value);
        if ($n === 'blood bank') return 'Blood Bank';
        if (str_contains($n, 'diagnostic')) return 'Diagnostic Center';
        if (str_contains($n, 'laboratory') || str_contains($n, 'lab')) return 'Laboratory';
        if (str_contains($n, 'clinic')) return 'Clinic';
        if (str_contains($n, 'hospital')) return 'Hospital';
        return $value;
    }

    private function normalizeType(string $value): string
    {
        $n = $this->norm($value);
        return match ($n) {
            'public', 'government', 'govt', 'govt.' => 'Government',
            'private' => 'Private',
            'ngo' => 'NGO',
            default => trim($value),
        };
    }


    private function divisionAlias(string $value): string
    {
        return match ($this->norm($value)) {
            'chattogram', 'chittagong' => 'Chattagram',
            'barishal' => 'Barisal',
            default => trim($value),
        };
    }

    private function districtAlias(string $value): string
    {
        return match ($this->norm($value)) {
            'cumilla' => 'Comilla',
            'barishal' => 'Barisal',
            'jhalokathi' => 'Jhalakathi',
            'cox s bazar', 'coxs bazar', 'cox bazar' => 'Coxsbazar',
            'netrakona' => 'Netrokona',
            'maulvibazar' => 'Moulvibazar',
            'unspecified' => '',
            default => trim($value),
        };
    }

    private function upazilaAlias(string $value): string
    {
        return match ($this->norm($value)) {
            'unspecified' => '',
            'raipura' => 'Roypura',
            'sirajdikhan' => 'Serajdikhan',
            'monirampur' => 'Manirampur',
            'ishwardi' => 'Ishurdi',
            'ullahpara' => 'Ullapara',
            default => trim($value),
        };
    }

    private function headerNorm(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim(mb_strtolower($value)));
    }

    private function norm(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = str_replace(['&'], ['and'], $value);
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
