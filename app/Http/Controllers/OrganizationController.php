<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\OrganizationCategory;
use App\Models\OrganizationType;
use App\Models\Division;
use App\Models\OrganizationContact;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union; 
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\CrmAccess;
use App\Models\User;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Sale;

class OrganizationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:org.view')->only([
            'index','datatable','show','profile','companyProfilePdfView','companyProfileDownload'
        ]);
        $this->middleware('permission:org.details.view')->only(['history']);
        $this->middleware('permission:org.create')->only(['store','quickCreate','quickStore']);
        $this->middleware('permission:org.edit')->only(['update']);
        $this->middleware('permission:org.delete')->only(['destroy']);
    }

    private function visibleOrganization(int $id): Organization
    {
        return CrmAccess::applyOrganizationScope(Organization::query())->findOrFail($id);
    }

    public function index()
    {
        $categories = OrganizationCategory::where('is_active',1)->orderBy('name')->get();
        $types      = OrganizationType::where('is_active',1)->orderBy('name')->get();
        $divisions  = Division::where('is_active',1)->orderBy('name')->get();

        $staffs = User::where('status',1)->orderBy('name')->get(['id','name']);
        return view('backend.content.organization.organizations.index', compact('categories','types','divisions','staffs'));
    }

    public function quickCreate()
    {
        $categories   = OrganizationCategory::where('is_active',1)->get();
        $types        = OrganizationType::where('is_active',1)->get();
        $divisions    = Division::where('is_active',1)->orderBy('name')->get();
        $departments  = Department::where('status','active')->get();
        $designations = Designation::where('status','active')->get();

        // Keep Create/Quick Create geo handling identical to the working Edit page.
        // District, Upazila and Union are loaded from the same geo AJAX endpoints.
        return view('backend.content.organization.organizations.quick_create', compact(
            'categories','types','divisions','departments','designations'
        ));
    }


    public function quickStore(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'organization_category_id' => 'required|exists:organization_categories,id',
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'no_of_beds' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'existing_machine' => 'required|string',
    
            'email'         => 'nullable|email',
            'phone_primary' => 'nullable|string|max:20',
            'phone_numbers' => 'nullable|array|max:10',
            'phone_numbers.*' => 'nullable|string|max:30',
            'email_addresses' => 'nullable|array|max:10',
            'email_addresses.*' => 'nullable|email|max:150',
            'map_location_link' => 'nullable|string|max:1000',
    
            'contacts' => 'nullable|array',
    
            'contacts.*.title'           => 'nullable|string|max:50',
            'contacts.*.name'            => 'nullable|string|max:255',
            'contacts.*.phone'           => 'nullable|string|max:20',
            'contacts.*.email'           => 'nullable|email',
            'contacts.*.phone_two'       => 'nullable|string|max:20',
            'contacts.*.phone_numbers'   => 'nullable|array|max:10',
            'contacts.*.phone_numbers.*' => 'nullable|string|max:30',
            'contacts.*.email_addresses' => 'nullable|array|max:10',
            'contacts.*.email_addresses.*' => 'nullable|email|max:150',
            'contacts.*.address'         => 'nullable|string|max:255',
            'contacts.*.additional_info' => 'nullable|string|max:255',
            'contacts.*.department_id'   => 'nullable|exists:departments,id',
            'contacts.*.designation_id'  => 'nullable|exists:designations,id',
            'contacts.*.image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'contacts.*.status'          => 'nullable|in:active,inactive',
        ]);
    
        if (CrmAccess::hasAreaRestriction()) {
            $request->validate(['district_id'=>'required|exists:districts,id','upazila_id'=>'required|exists:upazilas,id']);
            CrmAccess::ensureAreaAllowed((int)$request->district_id, (int)$request->upazila_id);
        }

        DB::beginTransaction();
    
        try {
    
            $org = Organization::create([
                'organization_category_id' => $request->organization_category_id,
                'organization_type_id'     => $request->organization_type_id,
                'name'                     => $request->name,
                'no_of_beds'               => $request->no_of_beds,
                'address'                  => $request->address,
                'division_id'              => $request->division_id,
                'district_id'              => $request->district_id,
                'upazila_id'               => $request->upazila_id,
                'union_id'                 => $request->union_id,
                'phone_primary'            => collect($request->phone_numbers ?? [$request->phone_primary])->filter()->values()->get(0),
                'phone_secondary'          => collect($request->phone_numbers ?? [])->filter()->values()->get(1),
                'phone_numbers'            => collect($request->phone_numbers ?? [$request->phone_primary])->filter()->values()->all(),
                'email'                    => collect($request->email_addresses ?? [$request->email])->filter()->values()->get(0),
                'email_addresses'          => collect($request->email_addresses ?? [$request->email])->filter()->values()->all(),
                'website'                  => $request->website,
                'map_location_link'        => $request->map_location_link,
                'notes'                    => $request->notes,
                'about_us'                 => $request->about_us,
                'existing_machine'          => $request->existing_machine,
                'status'                   => $request->status,
                'created_by'               => auth()->id(),
            ]);
    
            $contacts = $request->contacts ?? [];
            $primarySet = false;
    
            foreach ($contacts as $index => $c) {
    
                if (
                    empty($c['name']) &&
                    empty($c['phone']) && empty($c['email']) &&
                    empty(array_filter($c['phone_numbers'] ?? [])) &&
                    empty(array_filter($c['email_addresses'] ?? []))
                ) {
                    continue;
                }
    
                $imagePath = null;
    
                if (
                    $request->hasFile("contacts.$index.image") &&
                    $request->file("contacts.$index.image")->isValid()
                ) {
                
                    $file = $request->file("contacts.$index.image");
                
                    $fileName = time().'_'.$index.'_'.uniqid().'.'.$file->getClientOriginalExtension();
                
                    $file->move(
                        public_path('uploads/organization_contacts'),
                        $fileName
                    );
                
                    $imagePath = 'public/uploads/organization_contacts/'.$fileName;
                }
    
                $isPrimary = isset($c['is_primary']) ? 1 : 0;
    
                if ($isPrimary && $primarySet) {
                    $isPrimary = 0;
                }
    
                if ($isPrimary) {
                    $primarySet = true;
                }
    
                OrganizationContact::create([
                    'organization_id' => $org->id,
                    'title'           => $c['title'] ?? null,
                    'name'            => $c['name'] ?? null,
                    'phone'           => collect($c['phone_numbers'] ?? [$c['phone'] ?? null])->filter()->values()->get(0),
                    'email'           => collect($c['email_addresses'] ?? [$c['email'] ?? null])->filter()->values()->get(0),
                    'phone_two'       => collect($c['phone_numbers'] ?? [$c['phone'] ?? null, $c['phone_two'] ?? null])->filter()->values()->get(1),
                    'phone_numbers'   => collect($c['phone_numbers'] ?? [$c['phone'] ?? null, $c['phone_two'] ?? null])->filter()->values()->all(),
                    'email_addresses' => collect($c['email_addresses'] ?? [$c['email'] ?? null])->filter()->values()->all(),
                    'address'         => $c['address'] ?? null,
                    'additional_info' => $c['additional_info'] ?? null,
    
                    'department_id'   => $c['department_id'] ?? null,
                    'designation_id'  => $c['designation_id'] ?? null,
                    'image_url'           => $imagePath,
    
                    'status'          => $c['status'] ?? 'active',
                    'is_primary'      => $isPrimary,
                    'created_by'      => auth()->id(),
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'status'  => true,
                'message' => 'Organization & Contacts created successfully',
                'data'    => $org
            ]);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function datatable(Request $request)
    {
        $q = CrmAccess::applyOrganizationScope(Organization::query())
            ->with(['category:id,name','type:id,name','division:id,name','district:id,name','upazila:id,name','union:id,name','creator:id,name'])
            ->select('organizations.*')
            ->withCount(['activities','leads','sales'])
            ->selectSub(function ($sub) {
                $sub->from('lead_activities')
                    ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
                    ->whereColumn('leads.organization_id', 'organizations.id')
                    ->whereNull('leads.deleted_at')
                    ->selectRaw('COUNT(*)');
            }, 'followups_count')
            ->latest();

        // ✅ Filters
        if($request->filled('organization_category_id')) $q->where('organization_category_id', $request->organization_category_id);
        if($request->filled('organization_type_id'))     $q->where('organization_type_id', $request->organization_type_id);
        if($request->filled('division_id'))              $q->where('division_id', $request->division_id);
        if($request->filled('district_id'))              $q->where('district_id', $request->district_id);
        if($request->filled('upazila_id'))               $q->where('upazila_id', $request->upazila_id);
        if($request->filled('union_id'))                 $q->where('union_id', $request->union_id);
        if(auth()->user()->can('staff.filter') && $request->filled('created_by')) $q->where('created_by', $request->integer('created_by'));

        if($request->filled('name')) {
            $q->where('name', 'like', '%'.$request->name.'%');
        }

        if($request->filled('address')) {
            $q->where('address', 'like', '%'.$request->address.'%');
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('name', function($row){ return auth()->user()->can('org.details.view') ? '<a class="fw-semibold" href="'.route('org.history',$row->id).'">'.e($row->name).'</a>' : e($row->name); })
            ->addColumn('staff_name', fn($row) => e($row->creator?->name ?? '-'))
            ->addColumn('category', fn($row) => $row->category?->name ?? '-')
            ->addColumn('type', fn($row) => $row->type?->name ?? '-')
            ->addColumn('activity_summary', function($row){
                $has=(int)$row->activities_count > 0;
                return '<span class="badge '.($has?'bg-success':'bg-secondary').'">'.($has?'Yes':'No').'</span>'
                    .' <span class="text-muted small">('.(int)$row->activities_count.')</span>';
            })
            ->addColumn('history_summary', function($row){
                return '<div class="d-flex flex-wrap gap-1">'
                    .'<span class="badge bg-light text-dark border">A: '.(int)$row->activities_count.'</span>'
                    .'<span class="badge bg-light text-dark border">L: '.(int)$row->leads_count.'</span>'
                    .'<span class="badge bg-light text-dark border">F: '.(int)$row->followups_count.'</span>'
                    .'<span class="badge bg-light text-dark border">S: '.(int)$row->sales_count.'</span></div>';
            })
            ->addColumn('geo', function($row){
                $parts = array_filter([
                    $row->division?->name,
                    $row->district?->name,
                    $row->upazila?->name,
                    $row->union?->name,
                ]);
                return $parts ? implode('-> ', $parts) : '-';
            })
            ->editColumn('status', fn($row) =>
                $row->status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'
            )
            ->addColumn('action', function($row){
                $contactsUrl = route('org.contacts.index', $row->id);
                $editUrl = route('org.manage.show', $row->id);
                $html = '<div class="d-flex gap-2">';
                if (auth()->user()->can('org.view')) $html .= '<a href="'.$contactsUrl.'" class="btn btn-sm btn-light-brand" target="_blank" title="View"><i class="feather-eye"></i></a>';
                if (auth()->user()->can('org.edit')) $html .= '<a href="'.$editUrl.'" class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i></a>';
                if (auth()->user()->can('org.delete')) $html .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['name','activity_summary','history_summary','status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_category_id' => 'required|exists:organization_categories,id',
            'organization_type_id'     => 'nullable|exists:organization_types,id',
            'name'                     => 'required|string|max:200',
            'no_of_beds'               => 'nullable|integer|min:0',
            'address'                  => 'nullable|string|max:255',
            'division_id'              => 'required|exists:divisions,id',
            'district_id'              => 'required|exists:districts,id',
            'upazila_id'               => 'nullable|exists:upazilas,id',
            'union_id'                 => 'nullable|exists:unions,id',
            'phone_primary'            => 'nullable|string|max:30',
            'phone_secondary'          => 'nullable|string|max:30',
            'phone_numbers'            => 'nullable|array|max:10',
            'phone_numbers.*'          => 'nullable|string|max:30',
            'email'                    => 'nullable|email|max:150',
            'email_addresses'          => 'nullable|array|max:10',
            'email_addresses.*'        => 'nullable|email|max:150',
            'website'                  => 'nullable|string|max:150',
            'map_location_link'        => 'nullable|string|max:1000',
            'notes'                    => 'nullable|string',
            'about_us'                 => 'nullable|string',
            'existing_machine'         => 'required|string',
            'status'                   => 'required|in:active,inactive',
        ]);

        if (CrmAccess::hasAreaRestriction()) {
            validator($data, ['district_id'=>'required','upazila_id'=>'required'])->validate();
            CrmAccess::ensureAreaAllowed((int)$data['district_id'], (int)$data['upazila_id']);
        }
        $data['created_by'] = auth()->id();
        $data['phone_numbers'] = collect($request->phone_numbers ?? [$request->phone_primary])->filter()->values()->all();
        $data['email_addresses'] = collect($request->email_addresses ?? [$request->email])->filter()->values()->all();
        $data['phone_primary'] = $data['phone_numbers'][0] ?? null;
        $data['phone_secondary'] = $data['phone_numbers'][1] ?? null;
        $data['email'] = $data['email_addresses'][0] ?? null;

        Organization::create($data);

        return response()->json(['status'=>true,'message'=>'Organization created successfully']);
    }

    public function show($id)
    {
        $org = CrmAccess::applyOrganizationScope(Organization::with('contacts'))->findOrFail($id);

        $categories   = OrganizationCategory::where('is_active',1)->get();
        $types        = OrganizationType::where('is_active',1)->get();
        $divisions    = Division::where('is_active',1)->get();
        $districts    = District::where('division_id', $org->division_id)->where('is_active',1)->get();
        $upazilas     = Upazila::where('district_id', $org->district_id)->where('is_active',1)->get();
        $unions       = Union::where('upazila_id', $org->upazila_id)->where('is_active',1)->get();
    
        $departments  = Department::where('status','active')->get();
        $designations = Designation::where('status','active')->get();
    
        return view('backend.content.organization.organizations.edit', compact(
            'org',
            'categories',
            'types',
            'divisions',
            'districts',
            'upazilas',
            'unions',
            'departments',
            'designations'
        ));
    }

    public function update(Request $request, $id) 
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'organization_category_id' => 'required|exists:organization_categories,id',
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'no_of_beds' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'existing_machine' => 'required|string',
    
            'email'         => 'nullable|email',
            'phone_primary' => 'nullable|string|max:20',
            'phone_numbers' => 'nullable|array|max:10',
            'phone_numbers.*' => 'nullable|string|max:30',
            'email_addresses' => 'nullable|array|max:10',
            'email_addresses.*' => 'nullable|email|max:150',
    
            'contacts' => 'nullable|array',
    
            'contacts.*.id'              => 'nullable|exists:organization_contacts,id',
            'contacts.*.title'           => 'nullable|string|max:50',
            'contacts.*.name'            => 'nullable|string|max:255',
            'contacts.*.phone'           => 'nullable|string|max:20',
            'contacts.*.email'           => 'nullable|email',
            'contacts.*.phone_two'       => 'nullable|string|max:20',
            'contacts.*.phone_numbers'   => 'nullable|array|max:10',
            'contacts.*.phone_numbers.*' => 'nullable|string|max:30',
            'contacts.*.email_addresses' => 'nullable|array|max:10',
            'contacts.*.email_addresses.*' => 'nullable|email|max:150',
            'contacts.*.address'         => 'nullable|string|max:255',
            'contacts.*.additional_info' => 'nullable|string|max:255',
            'contacts.*.department_id'   => 'nullable|exists:departments,id',
            'contacts.*.designation_id'  => 'nullable|exists:designations,id',
            'contacts.*.image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'contacts.*.status'          => 'nullable|in:active,inactive',
            'map_location_link'         => 'nullable|string|max:1000',
    
            'deleted_contacts'           => 'nullable|array',
        ]);
    
        DB::beginTransaction();
    
        try {
    
            $org = $this->visibleOrganization((int)$id);
            if (CrmAccess::hasAreaRestriction()) {
                $request->validate(['district_id'=>'required|exists:districts,id','upazila_id'=>'required|exists:upazilas,id']);
                CrmAccess::ensureAreaAllowed((int)$request->district_id, (int)$request->upazila_id);
            }
    
            $org->update([
                'organization_category_id' => $request->organization_category_id,
                'organization_type_id'     => $request->organization_type_id,
                'name'                     => $request->name,
                'no_of_beds'               => $request->no_of_beds,
                'address'                  => $request->address,
                'division_id'              => $request->division_id,
                'district_id'              => $request->district_id,
                'upazila_id'               => $request->upazila_id,
                'union_id'                 => $request->union_id,
                'phone_primary'            => collect($request->phone_numbers ?? [$request->phone_primary])->filter()->values()->get(0),
                'phone_secondary'          => collect($request->phone_numbers ?? [])->filter()->values()->get(1),
                'phone_numbers'            => collect($request->phone_numbers ?? [$request->phone_primary])->filter()->values()->all(),
                'email'                    => collect($request->email_addresses ?? [$request->email])->filter()->values()->get(0),
                'email_addresses'          => collect($request->email_addresses ?? [$request->email])->filter()->values()->all(),
                'website'                  => $request->website,
                'map_location_link'        => $request->map_location_link,
                'notes'                    => $request->notes,
                'about_us'                 => $request->about_us,
                'existing_machine'          => $request->existing_machine,
                'status'                   => $request->status,
            ]);
    
            if ($request->deleted_contacts) {
                OrganizationContact::where('organization_id', $org->id)
                    ->whereIn('id', $request->deleted_contacts)
                    ->delete();
            }
    
            $contacts = $request->contacts ?? [];
            $primarySet = false;
    
            foreach ($contacts as $index => $c) {
    
                if (
                    empty($c['name']) &&
                    empty($c['phone']) && empty($c['email']) &&
                    empty(array_filter($c['phone_numbers'] ?? [])) &&
                    empty(array_filter($c['email_addresses'] ?? []))
                ) {
                    continue;
                }
    
                $contact = null;
    
                if (!empty($c['id'])) {
                    $contact = OrganizationContact::where('organization_id', $org->id)
                        ->where('id', $c['id'])
                        ->first();
                }
    
                $imagePath = $contact?->image_url;
    
                if (
                    $request->hasFile("contacts.$index.image") &&
                    $request->file("contacts.$index.image")->isValid()
                ) {
                
                    $file = $request->file("contacts.$index.image");
                
                    $fileName = time().'_'.$index.'_'.uniqid().'.'.$file->getClientOriginalExtension();
                
                    $file->move(
                        public_path('uploads/organization_contacts'),
                        $fileName
                    );
                
                    $imagePath = 'public/uploads/organization_contacts/'.$fileName;
                }
    
                $isPrimary = isset($c['is_primary']) ? 1 : 0;
    
                if ($isPrimary && $primarySet) {
                    $isPrimary = 0;
                }
    
                if ($isPrimary) {
                    $primarySet = true;
                }
    
                $data = [
                    'organization_id' => $org->id,
                    'title'           => $c['title'] ?? null,
                    'name'            => $c['name'] ?? null,
                    'phone'           => collect($c['phone_numbers'] ?? [$c['phone'] ?? null])->filter()->values()->get(0),
                    'email'           => collect($c['email_addresses'] ?? [$c['email'] ?? null])->filter()->values()->get(0),
                    'phone_two'       => collect($c['phone_numbers'] ?? [$c['phone'] ?? null, $c['phone_two'] ?? null])->filter()->values()->get(1),
                    'phone_numbers'   => collect($c['phone_numbers'] ?? [$c['phone'] ?? null, $c['phone_two'] ?? null])->filter()->values()->all(),
                    'email_addresses' => collect($c['email_addresses'] ?? [$c['email'] ?? null])->filter()->values()->all(),
                    'address'         => $c['address'] ?? null,
                    'additional_info' => $c['additional_info'] ?? null,
                    'department_id'   => $c['department_id'] ?? null,
                    'designation_id'  => $c['designation_id'] ?? null,
                    'image_url'           => $imagePath,
                    'status'          => $c['status'] ?? 'active',
                    'is_primary'      => $isPrimary,
                ];
    
                if ($contact) {
                    $contact->update($data);
                } else {
                    $data['created_by'] = auth()->id();
                    OrganizationContact::create($data);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'status'  => true,
                'message' => 'Organization & Contacts updated successfully',
                'data'    => $org
            ]);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    

    public function destroy(Organization $organization)
    {
        CrmAccess::ensureOrganizationAllowed($organization);
        $organization->delete();
        return response()->json(['status'=>true,'message'=>'Organization deleted successfully']);
    }
    
    public function profile($id)
    {
        $org = CrmAccess::applyOrganizationScope(Organization::with([
            'category',
            'type',
            'division',
            'district',
            'upazila',
            'union',
            'contacts.department',
            'contacts.designation',
        ]))->findOrFail($id);
    
        return view('backend.content.organization.organizations.profile', compact('org'));
    }
    
    
    public function companyProfilePdfView($id)
    {
        $org = CrmAccess::applyOrganizationScope(Organization::with([
            'category',
            'type',
            'division',
            'district',
            'upazila',
            'union',
            'contacts.department',
            'contacts.designation',
        ]))->findOrFail($id);
    
        $pdf = Pdf::loadView('backend.content.organization.organizations.profile_pdf', compact('org'))
            ->setPaper('a4', 'portrait');
    
        return $pdf->stream('company-profile-'.$org->id.'.pdf');
    }
    
    public function companyProfileDownload($id)
    {
        $org = CrmAccess::applyOrganizationScope(Organization::with([
            'category',
            'type',
            'division',
            'district',
            'upazila',
            'union',
            'contacts.department',
            'contacts.designation',
        ]))->findOrFail($id);
    
        $pdf = Pdf::loadView('backend.content.organization.organizations.profile_pdf', compact('org'))
            ->setPaper('a4', 'portrait');
    
        return $pdf->download('company-profile-'.$org->id.'.pdf');
    }


    // ✅ Geo dependent dropdown helpers (filter + modal both)
    public function districts(Request $request)
    {
        $request->validate(['division_id' => 'required|integer|exists:divisions,id']);

        $rows = District::where('division_id', $request->integer('division_id'))
            ->orderBy('name')
            ->get(['id','name']);

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function upazilas(Request $request)
    {
        $request->validate(['district_id' => 'required|integer|exists:districts,id']);

        $rows = Upazila::where('district_id', $request->integer('district_id'))
            ->orderBy('name')
            ->get(['id','name']);

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function unions(Request $request)
    {
        $request->validate(['upazila_id' => 'required|integer|exists:upazilas,id']);

        $rows = Union::where('upazila_id', $request->integer('upazila_id'))
            ->orderBy('name')
            ->get(['id','name']);

        return response()->json(['status' => true, 'data' => $rows]);
    }


    public function history(Request $request, Organization $organization)
    {
        $org = $this->visibleOrganization((int)$organization->id);
        $from=$request->date('from_date'); $to=$request->date('to_date'); $q=trim((string)$request->q);
        $range=function($query,$col='created_at')use($from,$to){if($from)$query->whereDate($col,'>=',$from);if($to)$query->whereDate($col,'<=',$to);return $query;};
        $activities=$range(Activity::with('creator')->where('organization_id',$org->id),'date'); if($q)$activities->where(fn($x)=>$x->where('contact_person','like',"%$q%")->orWhere('work_details','like',"%$q%"));
        $leads=$range(Lead::with('creator')->where('organization_id',$org->id)); if($q)$leads->where(fn($x)=>$x->where('lead_no','like',"%$q%")->orWhere('person_name','like',"%$q%"));
        $followups=$range(LeadActivity::with(['lead','creator'])->whereHas('lead',fn($x)=>$x->where('organization_id',$org->id)),'activity_at'); if($q)$followups->where('activity_text','like',"%$q%");
        $sales=$range(Sale::with('soldBy')->where('organization_id',$org->id),'sale_date'); if($q)$sales->where(fn($x)=>$x->where('sale_no','like',"%$q%")->orWhere('client_name','like',"%$q%"));
        $counts = [
            'activities' => (clone $activities)->count(),
            'leads' => (clone $leads)->count(),
            'followups' => (clone $followups)->count(),
            'sales' => (clone $sales)->count(),
        ];
        return view('backend.content.organization.organizations.history',['org'=>$org,'counts'=>$counts,'activities'=>$activities->latest('date')->paginate(15,['*'],'activities_page')->withQueryString(),'leads'=>$leads->latest()->paginate(15,['*'],'leads_page')->withQueryString(),'followups'=>$followups->latest('activity_at')->paginate(15,['*'],'followups_page')->withQueryString(),'sales'=>$sales->latest('sale_date')->paginate(15,['*'],'sales_page')->withQueryString()]);
    }

}
