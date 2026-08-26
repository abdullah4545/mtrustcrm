<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ clear cached permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ All permissions list (module wise)
        $permissions = [

            // Dashboard
            'dashboard.view',
            'dashboard.view_all_branches',
            'dashboard.view_branch',
            'dashboard.view_self',

            // Branch / Business
            'branch.manage',
            'business.manage',

            // Geo (mostly view only)
            'geo.view',

            // org
            'org.view',
            'org.create',
            'org.edit',
            'org.delete',
            'org_contact.manage',
            'org_category.manage',
            'org_type.manage',

            // Platform (Lead Source)
            'platform.manage',

            // StatusStage (Lead/Sales/Quotation stages)
            'status_stage.manage',

            // Leads
            'lead.view_all_branches',
            'lead.view_branch',
            'lead.view_self',
            'lead.create',
            'lead.edit',
            'lead.delete',
            'lead.activity.view',
            'lead.activity.add',

            // Field Activity / Visit
            'activity.view_all',
            'activity.view_branch',
            'activity.view_self',
            'activity.create',
            'activity.edit',
            'activity.delete',
            'activity.approve',

            // Products
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.category.manage',
            'product.subcategory.manage',
            'brand.manage',

            // Quotation
            'quotation.view_all_branches',
            'quotation.view_branch',
            'quotation.view_self',
            'quotation.create',
            'quotation.edit',
            'quotation.delete',
            'quotation.pdf',
            'quotation.mail',
            'quotation.convert_to_sale',

            // Sales
            'sale.view_all_branches',
            'sale.view_branch',
            'sale.view_self',
            'sale.create',
            'sale.edit',
            'sale.delete',
            'sale.pdf',
            'sale.mail',
            'sale.payment.view',
            'sale.payment.add',

            // Users / RBAC manage
            'user.view_all_branches',
            'user.view_branch',
            'user.create',
            'user.edit',
            'user.delete',
            'user.role.assign',
            'user.profile.update',

            'role.manage',
            'permission.manage',
            'database.backup.download',
        ];

        // ✅ Create permissions (guard web)
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // ✅ Create roles
        $roles = [
            'superadmin',
            'admin',
            'accounts',
            'manager',
            'branch_manager',
            'staff',
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // ✅ Role-wise permission assign
        $roleMap = [

            // ✅ Super Admin = everything
            'superadmin' => $permissions,

            // ✅ Admin (HQ/Admin) = everything but still ok to keep all
            'admin' => [
                'dashboard.view',
                'dashboard.view_all_branches',

                'branch.manage',
                'business.manage',
                'geo.view',

                'org.view','org.create','org.edit','org.delete',
                'org_contact.manage','org_category.manage','org_type.manage',

                'platform.manage',
                'status_stage.manage',

                'lead.view_all_branches','lead.create','lead.edit','lead.delete','lead.activity.view','lead.activity.add',
                'activity.view_all','activity.create','activity.edit','activity.delete','activity.approve',

                'product.view','product.create','product.edit','product.delete',
                'product.category.manage','product.subcategory.manage','brand.manage',

                'quotation.view_all_branches','quotation.create','quotation.edit','quotation.delete','quotation.pdf','quotation.mail','quotation.convert_to_sale',

                'sale.view_all_branches','sale.create','sale.edit','sale.delete','sale.pdf','sale.mail','sale.payment.view','sale.payment.add',

                'user.view_all_branches','user.create','user.edit','user.delete','user.role.assign','user.profile.update',

                'role.manage','permission.manage',
            ],

            // ✅ Accounts = Branch wise sales/payment + due/collection reports
            'accounts' => [
                'dashboard.view',
                'dashboard.view_branch',

                'geo.view',

                'lead.view_branch',
                'activity.view_branch','activity.create','activity.edit',

                'quotation.view_branch','quotation.pdf','quotation.mail',

                'sale.view_branch','sale.pdf','sale.mail',
                'sale.payment.view','sale.payment.add',

                'user.view_branch',
                'user.profile.update',
            ],

            // ✅ Manager = lead + quotation + sales monitoring (branch wide)
            'manager' => [
                'dashboard.view','dashboard.view_all_branches',
                'geo.view','org.view','org.create','org.edit','org_contact.manage',
                'platform.manage','status_stage.manage',
                'lead.view_all_branches','lead.create','lead.edit','lead.activity.view','lead.activity.add',
                'activity.view_all','activity.create','activity.edit','activity.approve',
                'quotation.view_all_branches','quotation.create','quotation.edit','quotation.pdf','quotation.mail','quotation.convert_to_sale',
                'sale.view_all_branches','sale.create','sale.edit','sale.pdf','sale.mail','sale.payment.view','sale.payment.add',
                'product.view','user.view_all_branches','user.profile.update',
            ],

            // ✅ Branch Manager = own branch full control (except global RBAC)
            'branch_manager' => [
                'dashboard.view',
                'dashboard.view_branch',

                'geo.view',

                'org.view','org.create','org.edit',
                'org_contact.manage',

                'platform.manage',
                'status_stage.manage',

                'lead.view_branch','lead.create','lead.edit','lead.delete','lead.activity.view','lead.activity.add',
                'activity.view_branch','activity.create','activity.edit','activity.delete','activity.approve',

                'quotation.view_branch','quotation.create','quotation.edit','quotation.delete','quotation.pdf','quotation.mail','quotation.convert_to_sale',

                'sale.view_branch','sale.create','sale.edit','sale.delete','sale.pdf','sale.mail',
                'sale.payment.view','sale.payment.add',

                'product.view',

                // ✅ user manage within branch
                'user.view_branch','user.create','user.edit','user.delete','user.role.assign','user.profile.update',
            ],

            // ✅ Staff = only self leads/activities (and view own quotation/sales if you allow)
            'staff' => [
                'dashboard.view',
                'dashboard.view_self',

                'geo.view',
                'org.view','org.create','org.edit','org_contact.manage',

                'lead.view_self','lead.create','lead.edit','lead.activity.view','lead.activity.add',
                'activity.view_self','activity.create','activity.edit',

                // optional: staff can create quotation/sale from lead (if you want)
                'quotation.view_self','quotation.create','quotation.edit','quotation.pdf',
                'sale.view_self','sale.create','sale.edit','sale.pdf',

                'user.profile.update',
            ],
        ];

        foreach ($roleMap as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            $role->syncPermissions($perms);
        }
    }
}