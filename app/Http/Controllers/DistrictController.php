<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DistrictController extends Controller
{
    public function __construct()
    { 
        $this->middleware('permission:geo.view')->only(['index','datatable','show']);
 
        $this->middleware('permission:geo.manage')->only(['store','update','destroy']);
 
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->can('geo.manage')) {
                return $next($request);
            } 
            abort_unless(auth()->user()?->can('geo.view'), 403);
            return $next($request);
        })->only(['index','datatable','show']);
    }
    public function index()
    {  
        $divisions = Division::select('id','name')->orderBy('name')->get();
        return view('backend.content.geo.districts.index', compact('divisions'));
    }

    public function datatable(Request $request)
    {
        $query = District::query()
            ->with('division:id,name')
            ->select(['id','division_id','name','code','is_active','created_at']);

        // ✅ Division filter
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('division', function ($row) {
                return $row->division?->name ?? '-';
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>
                    </div>
                ';
            })
            ->rawColumns(['is_active','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        // optional unique check in same division
        if (!empty($data['code'])) {
            $exists = District::where('division_id', $data['division_id'])
                ->where('code', $data['code'])->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'This code already exists under selected division.'
                ], 422);
            }
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $district = District::create($data);

        return response()->json([
            'status' => true,
            'message' => 'District created successfully',
            'data' => $district
        ]);
    }

    public function show(District $district)
    {
        return response()->json([
            'status' => true,
            'data' => $district
        ]);
    }

    public function update(Request $request, District $district)
    {
        $data = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        // optional unique check in same division
        if (!empty($data['code'])) {
            $exists = District::where('division_id', $data['division_id'])
                ->where('code', $data['code'])
                ->where('id', '!=', $district->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'This code already exists under selected division.'
                ], 422);
            }
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $district->update($data);

        return response()->json([
            'status' => true,
            'message' => 'District updated successfully',
            'data' => $district
        ]);
    }

    public function destroy(District $district)
    {
        $district->delete();

        return response()->json([
            'status' => true,
            'message' => 'District deleted successfully'
        ]);
    }
}
