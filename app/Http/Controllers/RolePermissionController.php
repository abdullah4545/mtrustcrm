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

    private function ensureRoleManageable(Role $role): void
    {
        if ($role->name === 'superadmin' && !auth()->user()->hasRole('superadmin')) abort(403);
    }

    private function allowedPermissionNames(): array
    {
        $u = auth()->user();
        if ($u->hasRole('superadmin')) return Permission::pluck('name')->all();
        return $u->getAllPermissions()->pluck('name')->all();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->ensureRoleManageable($role);

        $allowed = $this->allowedPermissionNames();
        $permissions = Permission::whereIn('name', $allowed)->orderBy('name')->get();
        $assigned = $role->permissions->pluck('name')->toArray();
        $grouped = $permissions->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'other');

        return view('backend.content.rbac.roles.permissions', compact('role','grouped','assigned'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $this->ensureRoleManageable($role);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $allowed = collect($this->allowedPermissionNames());
        $requested = collect($request->permissions ?? [])->unique();
        abort_if($requested->diff($allowed)->isNotEmpty(), 403, 'You cannot grant permissions that you do not have.');

        if (auth()->user()->hasRole('superadmin')) {
            $role->syncPermissions($requested->all());
        } else {
            // Preserve permissions outside the editor's authority; only modify allowed ones.
            $protected = $role->permissions->pluck('name')->diff($allowed);
            $role->syncPermissions($protected->merge($requested)->unique()->values()->all());
        }

        return back()->with('message', 'Permissions updated successfully.');
    }
}
