<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OrganizationCategoryController;
use App\Http\Controllers\OrganizationContactController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationImportController;
use App\Http\Controllers\OrganizationTypeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSubcategoryController;
use App\Http\Controllers\UpazilaController;
use App\Http\Controllers\UnionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StatusStageController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityReportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FollowupController;
use App\Http\Controllers\CrmReportController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\HeaderToolsController;
use Illuminate\Support\Facades\Route;



Route::middleware('auth')->group(function () { 
 
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/header/search', [HeaderToolsController::class, 'search'])->name('header.search');
    Route::get('/header/notifications', [HeaderToolsController::class, 'notifications'])->name('header.notifications');

    Route::get('/follow-ups', [FollowupController::class, 'index'])->name('followups.index');
    Route::post('/follow-ups/{id}/complete', [FollowupController::class, 'complete'])->name('followups.complete');

    Route::get('/reports/sales', [CrmReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/leads', [CrmReportController::class, 'leads'])->name('reports.leads');
    Route::get('/reports/collections', [CrmReportController::class, 'collections'])->name('reports.collections');
    
    Route::prefix('activities/report')->name('activities.report.')->group(function () {
        Route::get('/', [ActivityReportController::class,'index'])->name('index');
        Route::get('/data', [ActivityReportController::class,'data'])->name('data');
        Route::get('/pdf', [ActivityReportController::class,'pdf'])->name('pdf');
        Route::get('/print', [ActivityReportController::class,'print'])->name('print');
        Route::get('/excel', [ActivityReportController::class,'excel'])->name('excel');
    });
        
    // syatem
    Route::prefix('system')->group(function () {  

 
        // user
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('users/datatable', [UserManagementController::class, 'datatable'])->name('users.datatable');

        Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('users/{id}', [UserManagementController::class, 'show'])->name('users.show');

        Route::post('users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('users/{id}/delete', [UserManagementController::class, 'destroy'])->name('users.destroy');
    

        // ✅ ROLES
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/datatable', [RoleController::class, 'datatable'])->name('roles.datatable');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::post('roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('roles/{id}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');

        // ✅ PERMISSIONS
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/datatable', [PermissionController::class, 'datatable'])->name('permissions.datatable');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('permissions/{id}', [PermissionController::class, 'show'])->name('permissions.show');
        Route::post('permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::post('permissions/{id}/delete', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        // ✅ ROLE -> PERMISSION ASSIGN
        Route::get('roles/{id}/permissions', [RolePermissionController::class, 'edit'])->name('roles.permissions.edit');
        Route::post('roles/{id}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');
        // Branch
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/datatable', [BranchController::class, 'datatable'])->name('branches.datatable');

        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{id}', [BranchController::class, 'show'])->name('branches.show');

        Route::post('branches/{id}', [BranchController::class, 'update'])->name('branches.update'); 
        Route::post('branches/{id}/delete', [BranchController::class, 'destroy'])->name('branches.destroy');

        Route::get('database/backup', [DatabaseBackupController::class, 'download'])->name('database.backup.download');
        
    });

    // leads
    Route::get('leads', [LeadController::class,'index'])->name('leads.index');
    Route::get('leads/datatable', [LeadController::class,'datatable'])->name('leads.datatable');
    Route::get('leads/quick-create', [LeadController::class, 'quickCreate'])->name('leads.quickCreate');
    Route::post('leads/quick-store', [LeadController::class, 'quickStore'])->name('leads.quickStore');  
    Route::post('leads', [LeadController::class,'store'])->name('leads.store');
    Route::get('leads/{id}', [LeadController::class,'show'])->name('leads.show');
    Route::post('leads/{id}', [LeadController::class,'update'])->name('leads.update');
    Route::post('leads/{id}/delete', [LeadController::class,'destroy'])->name('leads.destroy');
    

    // organization dependent dropdown
    Route::get('organizations/options', [LeadController::class,'orgOptions'])->name('leads.org_options');
    Route::get('organizations/{id}/contacts', [LeadController::class,'orgContacts'])->name('leads.org_contacts');
    Route::get('organization-contacts/{id}', [LeadController::class,'contactDetails'])->name('leads.contact_details');

    // activities
    Route::get('leads/{id}/activities', [LeadController::class,'activities'])->name('leads.activities');
    Route::post('leads/{id}/activities', [LeadController::class,'storeActivity'])->name('leads.activities.store');

    // quotation pages
    Route::get('quotations', [QuotationController::class,'index'])->name('quotations.index');
    Route::get('quotations/datatable', [QuotationController::class,'datatable'])->name('quotations.datatable');

    Route::get('quotations/create', [QuotationController::class,'create'])->name('quotations.create');
    Route::post('quotations', [QuotationController::class,'store'])->name('quotations.store');

    Route::get('quotations/{id}/edit', [QuotationController::class,'edit'])->name('quotations.edit');
    Route::post('quotations/{id}', [QuotationController::class,'update'])->name('quotations.update');

    Route::get('quotations/{id}', [QuotationController::class,'show'])->name('quotations.show');
    Route::post('quotations/{id}/delete', [QuotationController::class,'destroy'])->name('quotations.destroy');

    Route::get('leads/{leadId}/quotation/create', [QuotationController::class,'createFromLead'])->name('leads.quotation.create');

    Route::get('quotations/{id}/pdf', [QuotationController::class,'pdf'])->name('quotations.pdf');
    Route::post('quotations/{id}/mail', [QuotationController::class,'sendMail'])->name('quotations.mail');

    Route::get('products/options', [QuotationController::class,'productOptions'])->name('products.options');
    Route::get('products/{id}/details', [QuotationController::class,'productDetails'])->name('products.details');

    // sales
    Route::get('sales', [SaleController::class,'index'])->name('sales.index');
    Route::get('sales/datatable', [SaleController::class,'datatable'])->name('sales.datatable');

    Route::get('sales/create', [SaleController::class,'create'])->name('sales.create');
    Route::post('sales', [SaleController::class,'store'])->name('sales.store');

    Route::get('sales/{id}', [SaleController::class,'show'])->name('sales.show');
    Route::get('sales/{id}/edit', [SaleController::class,'edit'])->name('sales.edit');
    Route::post('sales/{id}', [SaleController::class,'update'])->name('sales.update');
    Route::post('sales/{id}/delete', [SaleController::class,'destroy'])->name('sales.destroy');
    // Lead -> Sales
    Route::get('leads/{leadId}/sales/create', [SaleController::class,'createFromLead'])->name('leads.sales.create');
    // Quotation -> Sales
    Route::get('quotations/{qid}/sales/create', [SaleController::class,'createFromQuotation'])->name('quotations.sales.create');
    // PDF + Mail
    Route::get('sales/{id}/pdf', [SaleController::class,'pdf'])->name('sales.pdf');
    Route::post('sales/{id}/mail', [SaleController::class,'sendMail'])->name('sales.mail');
    // Payment add
    Route::post('sales/{id}/payment', [SaleController::class,'addPayment'])->name('sales.payment.add');

    // master geo
    Route::get('geo/districts/{division}', [GeoController::class, 'districts'])->name('geo.districts');
    Route::get('geo/upazilas/{district}', [GeoController::class, 'upazilas'])->name('geo.upazilas');
    Route::get('geo/unions/{upazila}', [GeoController::class, 'unions'])->name('geo.unions');

    Route::get('expense-types', [ExpenseTypeController::class,'index'])->name('expense-types.index');
    Route::post('expense-types', [ExpenseTypeController::class,'store'])->name('expense-types.store');
    Route::post('expense-types/{expenseType}', [ExpenseTypeController::class,'update'])->name('expense-types.update');
    Route::post('expense-types/{expenseType}/delete', [ExpenseTypeController::class,'destroy'])->name('expense-types.destroy');

    // activity
    Route::prefix('activities')->group(function () { 
        Route::get('/', [ActivityController::class, 'index'])->name('activities.index'); 
        Route::get('/datatable', [ActivityController::class, 'datatable'])->name('activities.datatable');
        Route::get('/quick-create', [ActivityController::class, 'quickCreate'])->name('activities.quick.create');
        Route::post('/quick-store', [ActivityController::class, 'quickStore'])->name('activities.quick.store'); 
        Route::post('/', [ActivityController::class, 'store'])->name('activities.store');
        Route::get('/{id}', [ActivityController::class, 'show'])->whereNumber('id')->name('activities.show');
        Route::post('/{id}', [ActivityController::class, 'update'])->whereNumber('id')->name('activities.update');
        Route::post('/{id}/delete', [ActivityController::class, 'destroy'])->whereNumber('id')->name('activities.destroy');
        Route::get('/ajax/organizations', [ActivityController::class, 'organizations']);
        Route::get('/ajax/departments', [ActivityController::class, 'departments']);
        Route::get('ajax/vehicles', [ActivityController::class,'vehicles'])->name('activities.vehicles');
        Route::get('ajax/expense-types', [ActivityController::class,'expenseTypes'])->name('activities.expense-types');
        Route::get('ajax/staffs', [ActivityController::class,'staffs'])->name('activities.staffs'); 
        Route::get('ajax/org-departments/{organization_id}', [ActivityController::class,'organizationDepartments'])
            ->name('activities.organization.departments'); 
        Route::get('ajax/org-contacts/{organization_id}/{department_id}', [ActivityController::class,'organizationContacts'])
            ->name('activities.organization.contacts');
       
    });

    // Department   
    Route::prefix('departments')->group(function () { 
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index'); 
        Route::get('/datatable', [DepartmentController::class, 'datatable'])->name('departments.datatable'); 
        Route::post('/store', [DepartmentController::class, 'store'])->name('departments.store'); 
        Route::get('/{id}/edit', [DepartmentController::class, 'show'])->name('departments.show'); 
        Route::post('/{id}', [DepartmentController::class, 'update'])->name('departments.update'); 
        Route::post('/{id}/delete', [DepartmentController::class, 'destroy'])->name('departments.delete');
    });
    
    // Designation
    Route::get('designations', [DesignationController::class,'index'])->name('designations.index');
    Route::get('designations/datatable', [DesignationController::class,'datatable'])->name('designations.datatable');
    Route::post('designations', [DesignationController::class,'store'])->name('designations.store');
    Route::get('designations/{id}', [DesignationController::class,'show'])
        ->whereNumber('id')
        ->name('designations.show');
    Route::post('designations/{id}', [DesignationController::class,'update'])
        ->whereNumber('id')
        ->name('designations.update');
    Route::post('designations/{id}/delete', [DesignationController::class,'destroy'])
        ->whereNumber('id')
        ->name('designations.destroy');
        
    // Vehicles
    Route::get('vehicles', [VehicleController::class,'index'])->name('vehicles.index');
    Route::get('vehicles/datatable', [VehicleController::class,'datatable'])->name('vehicles.datatable');
    Route::post('vehicles', [VehicleController::class,'store'])->name('vehicles.store'); 
    Route::get('vehicles/{id}/edit', [VehicleController::class,'edit'])
        ->whereNumber('id')
        ->name('vehicles.edit'); 
    Route::post('vehicles/{id}', [VehicleController::class,'update'])
        ->whereNumber('id')
        ->name('vehicles.update'); 
    Route::post('vehicles/{id}/delete', [VehicleController::class,'destroy'])
        ->whereNumber('id')
        ->name('vehicles.destroy');
        
    // geo
    Route::prefix('geo')->group(function () {
        // division
        Route::get('divisions', [DivisionController::class, 'index'])->name('divisions.index');
        Route::get('divisions/datatable', [DivisionController::class, 'datatable'])->name('divisions.datatable');

        Route::post('divisions', [DivisionController::class, 'store'])->name('divisions.store');
        Route::get('divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show');
        Route::put('divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
        Route::delete('divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy');
        
        // district
        Route::get('districts', [DistrictController::class, 'index'])->name('districts.index');
        Route::get('district/datatable', [DistrictController::class, 'datatable'])->name('districts.datatable');

        Route::post('districts', [DistrictController::class, 'store'])->name('districts.store');
        Route::get('district/{district}', [DistrictController::class, 'show'])->name('districts.show');
        Route::put('district/{district}', [DistrictController::class, 'update'])->name('districts.update');
        Route::delete('district/{district}', [DistrictController::class, 'destroy'])->name('districts.destroy');

        // upazila
        Route::get('upazilas', [UpazilaController::class, 'index'])->name('upazilas.index');
        Route::get('upazila/datatable', [UpazilaController::class, 'datatable'])->name('upazilas.datatable');

        Route::post('upazilas', [UpazilaController::class, 'store'])->name('upazilas.store');
        Route::get('upazila/{upazila}', [UpazilaController::class, 'show'])->name('upazilas.show');
        Route::put('upazila/{upazila}', [UpazilaController::class, 'update'])->name('upazilas.update');
        Route::delete('upazila/{upazila}', [UpazilaController::class, 'destroy'])->name('upazilas.destroy');

        Route::get('ajax/districts', [UpazilaController::class, 'ajaxDistricts'])->name('ajax.districts');

        // union
        Route::get('unions', [UnionController::class, 'index'])->name('unions.index');
        Route::get('union/datatable', [UnionController::class, 'datatable'])->name('unions.datatable');

        Route::post('unions', [UnionController::class, 'store'])->name('unions.store');
        Route::get('union/{union}', [UnionController::class, 'show'])->name('unions.show');
        Route::put('union/{union}', [UnionController::class, 'update'])->name('unions.update');
        Route::delete('union/{union}', [UnionController::class, 'destroy'])->name('unions.destroy');
  
        Route::get('ajax/upazilas', [UnionController::class, 'ajaxUpazilas'])->name('geo.ajax.upazilas');
        
    });

    // organization 
    Route::prefix('organization')->group(function () {
        // category
        Route::get('categories', [OrganizationCategoryController::class, 'index'])->name('org.categories.index');
        Route::get('categories/datatable', [OrganizationCategoryController::class, 'datatable'])->name('org.categories.datatable');

        Route::post('categories', [OrganizationCategoryController::class, 'store'])->name('org.categories.store');
        Route::get('categories/{organizationCategory}', [OrganizationCategoryController::class, 'show'])->name('org.categories.show');
        Route::put('categories/{organizationCategory}', [OrganizationCategoryController::class, 'update'])->name('org.categories.update');
        Route::delete('categories/{organizationCategory}', [OrganizationCategoryController::class, 'destroy'])->name('org.categories.destroy');

        // type
        Route::get('types', [OrganizationTypeController::class, 'index'])->name('org.types.index');
        Route::get('types/datatable', [OrganizationTypeController::class, 'datatable'])->name('org.types.datatable'); 
        Route::post('types', [OrganizationTypeController::class, 'store'])->name('org.types.store');
        Route::get('types/{organizationType}', [OrganizationTypeController::class, 'show'])->name('org.types.show');
        Route::put('types/{organizationType}', [OrganizationTypeController::class, 'update'])->name('org.types.update');
        Route::delete('types/{organizationType}', [OrganizationTypeController::class, 'destroy'])->name('org.types.destroy');

        // organization
        Route::get('manage', [OrganizationController::class, 'index'])->name('org.manage.index');
        Route::get('manage/datatable', [OrganizationController::class, 'datatable'])->name('org.manage.datatable');
        Route::post('manage/import/upload', [OrganizationImportController::class, 'upload'])->name('org.manage.import.upload');
        Route::post('manage/import/process', [OrganizationImportController::class, 'process'])->name('org.manage.import.process');

        Route::post('manage', [OrganizationController::class, 'store'])->name('org.manage.store');
        Route::get('manage/{organization}', [OrganizationController::class, 'show'])->name('org.manage.show');
        Route::post('manage/{organization}', [OrganizationController::class, 'update'])->name('org.manage.update');
        Route::delete('manage/{organization}', [OrganizationController::class, 'destroy'])->name('org.manage.destroy');

        Route::get('quick-create', [OrganizationController::class, 'quickCreate'])->name('org.quick.create'); 
        Route::post('quick-create', [OrganizationController::class, 'quickStore'])->name('org.quick.store');
            
        // dependent geo endpoints
        Route::get('geo/districts', [OrganizationController::class, 'districts'])->name('org.geo.districts');
        Route::get('geo/upazilas', [OrganizationController::class, 'upazilas'])->name('org.geo.upazilas');
        Route::get('geo/unions', [OrganizationController::class, 'unions'])->name('org.geo.unions');

        // contact
        Route::get('{organization}/view', [OrganizationController::class, 'profile'])
        ->name('org.contacts.index');
        Route::get('company-profile/{id}/pdf-view', [OrganizationController::class, 'companyProfilePdfView'])
            ->whereNumber('id')
            ->name('org.company.profile.pdf.view');
        
        Route::get('company-profile/{id}/download', [OrganizationController::class, 'companyProfileDownload'])
            ->whereNumber('id')
            ->name('org.company.profile.download');

        // datatable
        Route::get('{organization}/contacts/datatable', [OrganizationContactController::class, 'datatable'])
            ->name('org.contacts.datatable');

        // CRUD
        Route::post('{organization}/contacts', [OrganizationContactController::class, 'store'])
            ->name('org.contacts.store');

        Route::get('contacts/{contact}', [OrganizationContactController::class, 'show'])
            ->name('org.contacts.show');

        Route::post('contacts/{contact}', [OrganizationContactController::class, 'update'])
            ->name('org.contacts.update'); 

        Route::post('contacts/{contact}/delete', [OrganizationContactController::class, 'destroy'])
            ->name('org.contacts.destroy'); // using POST
            
    });

    // products
    Route::prefix('products')->group(function () {
        Route::get('categories', [ProductCategoryController::class, 'index'])->name('product.categories.index');
        Route::get('categories/datatable', [ProductCategoryController::class, 'datatable'])->name('product.categories.datatable');

        Route::post('categories', [ProductCategoryController::class, 'store'])->name('product.categories.store');
        Route::get('categories/{id}', [ProductCategoryController::class, 'show'])->name('product.categories.show');

        Route::post('categories/{id}', [ProductCategoryController::class, 'update'])->name('product.categories.update'); 
        Route::post('categories/{id}/delete', [ProductCategoryController::class, 'destroy'])->name('product.categories.destroy'); // POST

        // sub category
        Route::get('subcategories', [ProductSubcategoryController::class, 'index'])->name('product.subcategories.index');
        Route::get('subcategories/datatable', [ProductSubcategoryController::class, 'datatable'])->name('product.subcategories.datatable');

        Route::post('subcategories', [ProductSubcategoryController::class, 'store'])->name('product.subcategories.store');
        Route::get('subcategories/{id}', [ProductSubcategoryController::class, 'show'])->name('product.subcategories.show');

        Route::post('subcategories/{id}', [ProductSubcategoryController::class, 'update'])->name('product.subcategories.update'); 
        Route::post('subcategories/{id}/delete', [ProductSubcategoryController::class, 'destroy'])->name('product.subcategories.destroy'); // POST

        // brand
        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('brands/datatable', [BrandController::class, 'datatable'])->name('brands.datatable');

        Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('brands/{id}', [BrandController::class, 'show'])->name('brands.show');

        Route::post('brands/{id}', [BrandController::class, 'update'])->name('brands.update'); // POST + _method PUT
        Route::post('brands/{id}/delete', [BrandController::class, 'destroy'])->name('brands.destroy');

        // product
        Route::get('manage', [ProductController::class,'index'])->name('products.index');
        Route::get('manage/datatable', [ProductController::class,'datatable'])->name('products.datatable');

        Route::post('manage', [ProductController::class,'store'])->name('products.store');
        Route::get('manage/{id}/catalogue/view', [ProductController::class,'viewCatalogue'])->name('products.catalogue.view');
        Route::get('manage/{id}/catalogue/download', [ProductController::class,'downloadCatalogue'])->name('products.catalogue.download');
        Route::get('manage/{id}', [ProductController::class,'show'])->name('products.show');

        Route::post('manage/{id}', [ProductController::class,'update'])->name('products.update'); // POST + _method PUT
        Route::post('manage/{id}/delete', [ProductController::class,'destroy'])->name('products.destroy');

        // ✅ dependent dropdown: subcategories by category
        Route::get('sub-categories', [ProductController::class,'subcategoriesByCategory'])->name('product.subcategory');
        
    });

    // business
    Route::prefix('business')->group(function () {
        Route::get('settings', [BusinessController::class, 'index'])->name('settings.index');
        Route::post('settings', [BusinessController::class, 'update'])->name('settings.update');
        Route::get('seo', [BusinessController::class, 'seoindex'])->name('settings.seoindex');
        Route::post('seo', [BusinessController::class, 'seoupdate'])->name('settings.seoupdate');
        Route::get('social', [BusinessController::class, 'socialindex'])->name('settings.socialindex');
        Route::post('social', [BusinessController::class, 'socialupdate'])->name('settings.socialupdate');

        // status stage
        Route::get('status-stages', [StatusStageController::class,'index'])->name('status_stages.index');
        Route::get('status-stages/datatable', [StatusStageController::class,'datatable'])->name('status_stages.datatable');

        Route::post('status-stages', [StatusStageController::class,'store'])->name('status_stages.store');
        Route::get('status-stages/{id}', [StatusStageController::class,'show'])->name('status_stages.show');
        Route::post('status-stages/{id}', [StatusStageController::class,'update'])->name('status_stages.update');
        Route::post('status-stages/{id}/delete', [StatusStageController::class,'destroy'])->name('status_stages.destroy');

        // dropdown options
        Route::get('status-stages/options/{is_for}', [StatusStageController::class,'options'])
            ->name('status_stages.options');
        
        // platform
        Route::get('platforms', [PlatformController::class,'index'])->name('platforms.index');
        Route::get('platforms/datatable', [PlatformController::class,'datatable'])->name('platforms.datatable');

        Route::post('platforms', [PlatformController::class,'store'])->name('platforms.store');
        Route::get('platforms/{id}', [PlatformController::class,'show'])->name('platforms.show');

        Route::post('platforms/{id}', [PlatformController::class,'update'])->name('platforms.update');
        Route::post('platforms/{id}/delete', [PlatformController::class,'destroy'])->name('platforms.destroy');
        
    });


});

require __DIR__.'/auth.php';
