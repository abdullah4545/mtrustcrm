<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
class DivisionController extends Controller
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
        return view('backend.content.geo.divisions.index');
    }

    // ✅ Yajra server-side datatable + search
    public function datatable(Request $request)
    {
        $query = Division::query()->select(['id','name','code','is_active','created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
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
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20|unique:divisions,code',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $division = Division::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Division created successfully',
            'data' => $division
        ]);
    }

    public function show(Division $division)
    {
        return response()->json([
            'status' => true,
            'data' => $division
        ]);
    }

    public function update(Request $request, Division $division)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20|unique:divisions,code,'.$division->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $division->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Division updated successfully',
            'data' => $division
        ]);
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return response()->json([
            'status' => true,
            'message' => 'Division deleted successfully'
        ]);
    }
}
