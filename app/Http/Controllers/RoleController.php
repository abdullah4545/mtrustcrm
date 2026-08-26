<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role.manage');
    }

    public function index()
    {
        return view('backend.content.rbac.roles.index');
    }

    public function datatable()
    {
        $q = Role::query();

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('permissions', function($row){
                return $row->permissions()->count();
            })
            ->addColumn('action', function($row){
                return '
                    <a class="btn btn-sm btn-dark mb-2 mr-2" href="'.route('roles.permissions.edit',$row->id).'">
                        <i class="feather-lock"></i>
                    </a>
                    <button class="btn btn-sm btn-primary btn-edit mb-2 mr-2" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete mb-2" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->addColumn('permissions', function($row){
                $names = $row->permissions()->pluck('name')->toArray();
                if(empty($names)) return '<span class="text-muted">—</span>';

                $show = array_slice($names, 0, 6);
                $html = '';
                foreach ($show as $p) {
                    $html .= '<span class="badge bg-light text-dark border me-1 mb-1" style="font-size:12px;">'.$p.'</span>';
                }
                if(count($names) > 6){
                    $more = count($names) - 6;
                    $html .= '<span class="badge bg-secondary mb-1" style="font-size:12px;">+'.$more.' more</span>';
                }
                return $html;
            })
            ->rawColumns(['permissions','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        Role::create(['name' => strtolower(trim($request->name))]);

        return response()->json(['status'=>true,'message'=>'Role created successfully']);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$role]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,'.$role->id,
        ]);

        $role->name = strtolower(trim($request->name));
        $role->save();

        return response()->json(['status'=>true,'message'=>'Role updated successfully']);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // safety: prevent deleting superadmin
        if ($role->name === 'superadmin') {
            return response()->json(['status'=>false,'message'=>'superadmin role cannot be deleted'], 422);
        }

        $role->delete();
        return response()->json(['status'=>true,'message'=>'Role deleted successfully']);
    }
}