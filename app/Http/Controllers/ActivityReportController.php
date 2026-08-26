<?php

namespace App\Http\Controllers;

use App\Exports\ActivityReportExport;
use App\Models\Activity;
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
        $this->middleware('permission:activity.view_all|activity.view_branch|activity.view_self');
    }

    
    private const AVAILABLE_COLUMNS = [
        'serial'            => 'Sl',
        'date'              => 'Date',
        'organization_name' => 'Name of Organ.',
        'department'        => 'Depart.',
        'contact_person'    => 'Cont. Person',
        'details'           => 'Details',
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
        'created_by'        => 'Created By',
    ];

    
    private const DEFAULT_COLUMNS = [
        'serial',
        'date',
        'organization_name',
        'department',
        'contact_person',
        'details',
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
        
        $userQuery = DB::table('users')->select('id','name')->orderBy('name');
        if (Auth::user()->can('activity.view_branch') && !Auth::user()->can('activity.view_all')) {
            $userQuery->where('branch_id', Auth::user()->branch_id);
        } elseif (Auth::user()->can('activity.view_self') && !Auth::user()->can('activity.view_branch') && !Auth::user()->can('activity.view_all')) {
            $userQuery->where('id', Auth::id());
        }
        $users = $userQuery->get();

        $organizationQuery = DB::table('activities')
            ->whereNotNull('organization_id')
            ->whereNotNull('organization_name');
        if (Auth::user()->can('activity.view_branch') && !Auth::user()->can('activity.view_all')) {
            $organizationQuery->where('branch_id', Auth::user()->branch_id);
        } elseif (Auth::user()->can('activity.view_self') && !Auth::user()->can('activity.view_branch') && !Auth::user()->can('activity.view_all')) {
            $organizationQuery->where('created_by', Auth::id());
        }
        $organizations = $organizationQuery
            ->select('organization_id', 'organization_name')
            ->distinct()
            ->orderBy('organization_name')
            ->get();

        return view('backend.content.activity.report.index', [
            'users'            => $users,
            'organizations'    => $organizations,
            'availableColumns' => self::AVAILABLE_COLUMNS,
            'defaultColumns'   => self::DEFAULT_COLUMNS,
        ]);
    }

    /**
     * AJAX report table data.
     */
    public function data(Request $request)
    {
        $validated = $this->validateFilters($request);

        $columns = $this->resolveColumns(
            $validated['columns'] ?? []
        );

        $activities = $this->reportQuery($validated)
            ->with('creator')
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

        $columns = $this->resolveColumns(
            $validated['columns'] ?? []
        );

        $activities = $this->reportQuery($validated)
            ->with('creator')
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

        $columns = $this->resolveColumns(
            $validated['columns'] ?? []
        );

        $activities = $this->reportQuery($validated)
            ->with('creator')
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
        if ($u->can('activity.view_all')) {
            // requested branch/user filters are allowed
        } elseif ($u->can('activity.view_branch')) {
            $validated['branch_id'] = $u->branch_id;
        } else {
            $validated['branch_id'] = $u->branch_id;
            $validated['created_by'] = $u->id;
        }

        return $validated;
    }

    private function resolveColumns(array $columns): array
    {
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
        $row = [];

        foreach ($columns as $column) {
            $row[$column] = match ($column) {
                'serial' => $serial,

                'date' => optional($activity->date)
                    ->format('D, M d, Y'),

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

                'status' => ucfirst(
                    (string) $activity->status
                ),

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