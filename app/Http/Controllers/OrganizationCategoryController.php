<?php

namespace App\Http\Controllers;

use App\Models\OrganizationCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrganizationCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:org_category.manage')
            ->only(['index','datatable','store','show','update','destroy']);
    }

    public function index()
    {
        return view('backend.content.organization.categories.index');
    }

    public function datatable()
    {
        $query = OrganizationCategory::query()
            ->select(['id','name','description','is_active','created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('description', function($row){
                return $row->description ? \Illuminate\Support\Str::limit($row->description, 60) : '-';
            })
            ->editColumn('is_active', fn($row) =>
                $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'
            )
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
            'name'        => 'required|string|max:150|unique:organization_categories,name',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool)($request->is_active ?? 0);

        OrganizationCategory::create($data);

        return response()->json(['status'=>true,'message'=>'Organization category created successfully']);
    }

    public function show(OrganizationCategory $organizationCategory)
    {
        return response()->json(['status'=>true,'data'=>$organizationCategory]);
    }

    public function update(Request $request, OrganizationCategory $organizationCategory)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150|unique:organization_categories,name,'.$organizationCategory->id,
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $organizationCategory->update($data);

        return response()->json(['status'=>true,'message'=>'Organization category updated successfully']);
    }

    public function destroy(OrganizationCategory $organizationCategory)
    {
        $organizationCategory->delete();
        return response()->json(['status'=>true,'message'=>'Organization category deleted successfully']);
    }
}
