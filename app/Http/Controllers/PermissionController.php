<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permission.manage');
    }

    public function index()
    {
        return view('backend.content.rbac.permissions.index');
    }

    public function datatable()
    {
        $q = Permission::query()->latest();

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('roles', function($row){
                return $row->roles()->count();
            })
            ->addColumn('action', function($row){
                return '
                    <button class="btn btn-sm btn-primary btn-edit mb-2 mr-2" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete mb-2" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:permissions,name',
        ]);

        Permission::create(['name' => strtolower(trim($request->name))]);

        return response()->json(['status'=>true,'message'=>'Permission created successfully']);
    }

    public function show($id)
    {
        $p = Permission::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$p]);
    }

    public function update(Request $request, $id)
    {
        $p = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150|unique:permissions,name,'.$p->id,
        ]);

        $p->name = strtolower(trim($request->name));
        $p->save();

        return response()->json(['status'=>true,'message'=>'Permission updated successfully']);
    }

    public function destroy($id)
    {
        $p = Permission::findOrFail($id);
        $p->delete();
        return response()->json(['status'=>true,'message'=>'Permission deleted successfully']);
    }
}