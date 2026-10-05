<?php

namespace App\Http\Controllers;

use App\Exports\ActivityReportExport;
use App\Models\Activity;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:report.activity.view');
        $this->middleware('permission:report.view_all|report.view_branch|report.view_self');
    }

    
    private const AVAILABLE_COLUMNS = [
        'serial'            => 'Sl',
        'date'              => 'Date & Time',
        'organization_name' => 'Name of Organ.',
        'department'        => 'Depart.',
        'contact_person'    => 'Cont. Person',
        'from_location'     => 'From',
        'to_location'       => 'To',
        'distance'          => 'Dist',
        'vehicle'           => 'Vehicles',
        'work_details'      => 'Vis. Output',
        'ta'                => 'TA',
        'da'                => 'DA',
        'total'             => 'Total',
        'remarks'           => 'Remarks',
        'status'            => 'Status',
        'payment_status'    => 'Payment Status',
        'tour_type'         => 'Tour Type',
        'created_by'        => 'Created By',
    ];

    
    private const DEFAULT_COLUMNS = [
        'serial',
        'date',
        'organization_name',
        'department',
        'contact_person',
        'from_location',
        'to_location',
        'distance',
        'vehicle',
        'work_details',
        'ta',
        'da',
        'total',
        'remarks',
    ];

    public function index(Request $request)
    {
        $reportFilters = session('activity_report_filters', []);
        $selectedOrganization = null;
        if (!empty($reportFilters['organization_id'])) {
            $selectedOrganization = DB::table('organizations')->where('id', $reportFilters['organization_id'])->select('id','name')->first();
        }

        $userQuery = DB::table('users')->select('id','name')->orderBy('name');
        if (!Auth::user()->can('staff.filter')) $userQuery->whereRaw('1 = 0');
        if (!Auth::user()->can('report.view_all')) {
            $userQuery->where('id', Auth::id());
        }
        $users = $userQuery->get();

        $branchQuery = Branch::query()->orderBy('branch_name');
        if (!Auth::user()->can('report.view_all')) $branchQuery->whereKey(Auth::user()->branch_id);
        $branches = $branchQuery->get(['id','branch_name','branch_code']);

        return view('backend.content.activity.report.index', [
            'users'            => $users,
            'branches'         => $branches,
            'availableColumns' => self::AVAILABLE_COLUMNS,
            'defaultColumns'   => self::DEFAULT_COLUMNS,
            'canViewStatus'    => Auth::user()->can('activity.status.view'),
            'canManagePayment' => Auth::user()->can('activity.payment.manage'),
            'reportFilters'     => $reportFilters,
            'selectedOrganization' => $selectedOrganization,
        ]);
    }

    /**
     * AJAX report table data.
     */
    public function data(Request $request)
    {
        $validated = $this->validateFilters($request);

        // Persist report filters server-side. Column choices are intentionally separate.
        session(['activity_report_filters' => [
            'from_date' => (string) ($validated['from_date'] ?? ''),
            'to_date' => (string) ($validated['to_date'] ?? ''),
            'created_by' => (string) ($validated['created_by'] ?? ''),
            'payment_status' => (string) ($validated['payment_status'] ?? ''),
            'status' => (string) ($validated['status'] ?? ''),
            'organization_id' => (string) ($validated['organization_id'] ?? ''),
            'branch_id' => (string) ($validated['branch_id'] ?? ''),
        ]]);

        $columns = $this->resolveColumns(
            $validated['columns'] ?? []
        );

        $activities = $this->reportQuery($validated)
            ->with(['creator','travels'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,

            'columns' => collect($columns)
                ->map(fn ($column) => [
                    'key'   => $column,
                    'label' => self::AVAILABLE_COLUMNS[$column],
                ])
                ->values(),

            'rows' => $activities
                ->values()
                ->map(function (Activity $activity, int $index) use ($columns) {
                    return $this->formatActivityRow(
                        $activity,
                        $columns,
                        $index + 1
                    );
                }),

            'summary' => [
                'activity_count' => $activities->count(),
                'total_ta'       => number_format(
                    (float) $activities->sum('ta'),
                    2
                ),
                'total_da'       => number_format(
                    (float) $activities->sum('da'),
                    2
                ),
                'grand_total'    => number_format(
                    (float) $activities->sum('total'),
                    2
                ),
            ],
        ]);
    }

  
    public function pdf(Request $request)
    {
        $validated = $this->validateFilters($request);

        $columns = $this->resolveColumns($validated['columns'] ?? []);

        $activities = $this->reportQuery($validated)
            ->with(['creator','travels'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $employee = $this->getSelectedEmployee(
            $validated['created_by'] ?? null
        );

        $fromDate = !empty($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->format('d-m-Y')
            : null;

        $toDate = !empty($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->format('d-m-Y')
            : null;

        $pdf = Pdf::loadView('backend.content.activity.report.pdf', [
            'activities' => $activities,
            'employee'    => $employee,
            'columns'     => $columns,
            'columnLabels'=> self::AVAILABLE_COLUMNS,
            'fromDate'    => $fromDate,
            'toDate'      => $toDate,
            'totalTa'     => $activities->sum('ta'),
            'totalDa'     => $activities->sum('da'),
            'grandTotal'  => $activities->sum('total'),
            'statusFilter'=> $validated['status'] ?? null,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'activity-report-' .
            now()->format('Y-m-d-His') .
            '.pdf';

        return $pdf->download($filename);
    }

  
    public function print(Request $request)
    {
        $validated = $this->validateFilters($request);

        $columns = $this->resolveColumns($validated['columns'] ?? []);

        $activities = $this->reportQuery($validated)
            ->with(['creator','travels'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $employee = $this->getSelectedEmployee(
            $validated['created_by'] ?? null
        );

        $pdf = Pdf::loadView('backend.content.activity.report.pdf', [
            'activities'  => $activities,
            'employee'    => $employee,
            'columns'     => $columns,
            'columnLabels'=> self::AVAILABLE_COLUMNS,
            'fromDate'    => !empty($validated['from_date'])
                ? Carbon::parse($validated['from_date'])->format('d-m-Y')
                : null,
            'toDate'      => !empty($validated['to_date'])
                ? Carbon::parse($validated['to_date'])->format('d-m-Y')
                : null,
            'totalTa'     => $activities->sum('ta'),
            'totalDa'     => $activities->sum('da'),
            'grandTotal'  => $activities->sum('total'),
            'statusFilter'=> $validated['status'] ?? null,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('activity-report.pdf');
    }

    /**
     * Excel download.
     */
    public function excel(Request $request): BinaryFileResponse
    {
        $validated = $this->validateFilters($request);

        $columns = $this->resolveColumns(
            $validated['columns'] ?? []
        );

        $filename = 'activity-report-' .
            now()->format('Y-m-d-His') .
            '.xlsx';

        return Excel::download(
            new ActivityReportExport(
                filters: $validated,
                columns: $columns,
                columnLabels: self::AVAILABLE_COLUMNS
            ),
            $filename
        );
    }

    private function reportQuery(array $filters): Builder
    {
        return Activity::query()
            ->when(
                !empty($filters['from_date']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'date',
                        '>=',
                        $filters['from_date']
                    )
            )
            ->when(
                !empty($filters['to_date']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'date',
                        '<=',
                        $filters['to_date']
                    )
            )
            ->when(
                !empty($filters['created_by']),
                fn (Builder $query) =>
                    $query->where(
                        'created_by',
                        $filters['created_by']
                    )
            )
            ->when(
                !empty($filters['status']),
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $filters['status']
                    )
            )
            ->when(!empty($filters['payment_status']), fn (Builder $query) => $query->where('payment_status',$filters['payment_status']))
            ->when(
                !empty($filters['organization_id']),
                fn (Builder $query) =>
                    $query->where(
                        'organization_id',
                        $filters['organization_id']
                    )
            )
            ->when(
                !empty($filters['branch_id']),
                fn (Builder $query) =>
                    $query->where(
                        'branch_id',
                        $filters['branch_id']
                    )
            );
    }

    private function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'created_by' => [
                'nullable',
                'integer',
            ],

            'organization_id' => [
                'nullable',
                'integer',
            ],

            'branch_id' => [
                'nullable',
                'integer',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                ]),
            ],

            'payment_status' => ['nullable', Rule::in(['unpaid','waiting_for_payment','paid'])],

            'columns' => [
                'nullable',
                'array',
            ],

            'columns.*' => [
                'string',
                Rule::in(array_keys(self::AVAILABLE_COLUMNS)),
            ],
        ]);

        $u = Auth::user();
        if (!$u->can('activity.status.view')) {
            unset($validated['status']);
            if (!empty($validated['columns'])) $validated['columns'] = array_values(array_diff($validated['columns'], ['status']));
        }
        if (!$u->can('staff.filter')) {
            unset($validated['created_by']);
        }

        if (!$u->can('report.view_all')) {
            $validated['branch_id'] = $u->branch_id;
            $validated['created_by'] = $u->id;
        }

        return $validated;
    }

    private function resolveColumns(array $columns): array
    {
        if (!Auth::user()->can('activity.status.view')) $columns = array_values(array_diff($columns, ['status']));
        $validColumns = array_values(
            array_intersect(
                $columns,
                array_keys(self::AVAILABLE_COLUMNS)
            )
        );

        return !empty($validColumns)
            ? $validColumns
            : self::DEFAULT_COLUMNS;
    }

    private function formatActivityRow(
        Activity $activity,
        array $columns,
        int $serial
    ): array {
        $row = [
            '_id' => $activity->id,
            '_status' => strtolower((string) $activity->status),
            '_payment_status' => (string) $activity->payment_status,
            '_payment_eligible' => strtolower((string) $activity->status) !== 'rejected',
        ];

        foreach ($columns as $column) {
            $row[$column] = match ($column) {
                'serial' => $serial,

                'date' => ($activity->activity_at ?? $activity->created_at ?? $activity->date)
                    ? ($activity->activity_at ?? $activity->created_at ?? $activity->date)
                        ->copy()->timezone('Asia/Dhaka')->format('d M Y, h:i A')
                    : '',

                'distance' => $this->formatDistance(
                    $activity->distance
                ),

                'ta',
                'da',
                'total' => number_format(
                    (float) $activity->{$column},
                    2
                ),

                'created_by' => optional(
                    $activity->creator
                )->name ?? 'N/A',

                'status' => ucfirst((string) $activity->status),
                'payment_status' => ucwords(str_replace('_',' ',(string)$activity->payment_status)),
                'tour_type' => $activity->travels->pluck('tour_type')->map(fn($v)=>$v==='tour'?'Tour':'Local Tour')->unique()->implode(', '),

                default => $activity->{$column} ?? '',
            };
        }

        return $row;
    }

    private function formatDistance($distance): string
    {
        if ($distance === null || $distance === '') {
            return '';
        }

        $number = (float) $distance;

        if ($number == floor($number)) {
            return (string) (int) $number;
        }

        return number_format($number, 2);
    }
 
    private function getSelectedEmployee(?int $employeeId): ?object
    {
        if (!$employeeId) {
            return null;
        }

         
        return DB::table('users')
            ->where('id', $employeeId)
            ->first();
    }
}