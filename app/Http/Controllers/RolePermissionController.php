<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role.manage');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $assigned = $role->permissions->pluck('name')->toArray();

        // group by module (before first dot)
        $grouped = $permissions->groupBy(function($p){
            return explode('.', $p->name)[0] ?? 'other';
        });

        return view('backend.content.rbac.roles.permissions', compact('role','grouped','assigned'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return back()->with('message', 'Permissions updated successfully.');
    }
}