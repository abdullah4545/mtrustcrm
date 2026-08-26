<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ url('/') }}" class="b-brand">
                <img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" alt="{{ $business?->business_name ?? 'Medi Trust Solution' }}" class="logo logo-lg" style="width:100%" />
                <img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" alt="" class="logo logo-sm" style="width:100%" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption"><label>Navigation</label></li>

                @can('dashboard.view')
                <li class="nxl-item">
                    <a href="{{ route('dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboards</span>
                    </a>
                </li>
                @endcan

                @canany(['activity.view_all','activity.view_branch','activity.view_self','activity.create'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-map-pin"></i></span>
                        <span class="nxl-mtext">Field Activity</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('activity.create')<li class="nxl-item"><a class="nxl-link" href="{{ route('activities.quick.create') }}">Activity Entry</a></li>@endcan
                        @canany(['activity.view_all','activity.view_branch','activity.view_self'])<li class="nxl-item"><a class="nxl-link" href="{{ route('activities.index') }}">Field Activity</a></li>@endcanany
                    </ul>
                </li>
                @endcanany

                @canany(['lead.view_all_branches','lead.view_branch','lead.view_self','lead.create'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-target"></i></span>
                        <span class="nxl-mtext">Leads</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])<li class="nxl-item"><a class="nxl-link" href="{{ route('leads.index') }}">Leads</a></li>@endcanany
                        @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])<li class="nxl-item"><a class="nxl-link" href="{{ route('followups.index') }}">Follow-ups</a></li>@endcanany
                        @can('lead.create')<li class="nxl-item"><a class="nxl-link" href="{{ route('leads.quickCreate') }}">Lead Entry</a></li>@endcan
                    </ul>
                </li>
                @endcanany

                @canany(['quotation.view_all_branches','quotation.view_branch','quotation.view_self','quotation.create'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Quotations</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @canany(['quotation.view_all_branches','quotation.view_branch','quotation.view_self'])<li class="nxl-item"><a class="nxl-link" href="{{ route('quotations.index') }}">Quotations</a></li>@endcanany
                        @can('quotation.create')<li class="nxl-item"><a class="nxl-link" href="{{ route('quotations.create') }}">Create Quotation</a></li>@endcan
                    </ul>
                </li>
                @endcanany

                @canany(['sale.view_all_branches','sale.view_branch','sale.view_self','sale.create'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-shopping-cart"></i></span>
                        <span class="nxl-mtext">Sales</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @canany(['sale.view_all_branches','sale.view_branch','sale.view_self'])<li class="nxl-item"><a class="nxl-link" href="{{ route('sales.index') }}">Sales</a></li>@endcanany
                        @can('sale.create')<li class="nxl-item"><a class="nxl-link" href="{{ route('sales.create') }}">Create Sale</a></li>@endcan
                    </ul>
                </li>
                @endcanany

                @canany(['org.view','org.create','org.edit','org_category.manage','org_type.manage'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-layout"></i></span>
                        <span class="nxl-mtext">Organization</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('org_category.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('org.categories.index') }}">Category</a></li>@endcan
                        @can('org_type.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('org.types.index') }}">Type</a></li>@endcan
                        @canany(['org.view','org.create','org.edit'])<li class="nxl-item"><a class="nxl-link" href="{{ route('org.manage.index') }}">Organization</a></li>@endcanany
                    </ul>
                </li>
                @endcanany

                @canany(['sale.view_all_branches','sale.view_branch','lead.view_all_branches','lead.view_branch','activity.view_all','activity.view_branch'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">Reports</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @canany(['sale.view_all_branches','sale.view_branch'])<li class="nxl-item"><a class="nxl-link" href="{{ route('reports.sales') }}">Sales Report</a></li>@endcanany
                        @canany(['lead.view_all_branches','lead.view_branch'])<li class="nxl-item"><a class="nxl-link" href="{{ route('reports.leads') }}">Leads Report</a></li>@endcanany
                        @canany(['sale.view_all_branches','sale.view_branch'])<li class="nxl-item"><a class="nxl-link" href="{{ route('reports.collections') }}">Collection Report</a></li>@endcanany
                        @canany(['activity.view_all','activity.view_branch'])<li class="nxl-item"><a class="nxl-link" href="{{ route('activities.report.index') }}">Activity Report</a></li>@endcanany
                    </ul>
                </li>
                @endcanany

                @canany(['product.view','product.create','product.edit','product.category.manage','brand.manage'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                        <span class="nxl-mtext">Products</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('product.category.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('product.categories.index') }}">Categorys</a></li>@endcan
                        @can('product.subcategory.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('product.subcategories.index') }}">Sub Categorys</a></li>@endcan
                        @can('brand.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('brands.index') }}">Brands</a></li>@endcan
                        @canany(['product.view','product.create','product.edit'])<li class="nxl-item"><a class="nxl-link" href="{{ route('products.index') }}">Products</a></li>@endcanany
                    </ul>
                </li>
                @endcanany

                @can('geo.view')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-map"></i></span>
                        <span class="nxl-mtext">Geo Masters</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('divisions.index') }}">Divisions</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('districts.index') }}">Districts</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('upazilas.index') }}">Upazilas</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('unions.index') }}">Unions</a></li>
                    </ul>
                </li>
                @endcan

                @canany(['user.view_all_branches','user.view_branch','user.create','user.edit','role.manage','permission.manage','branch.view_all','branch.manage','database.backup.download'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-cast"></i></span>
                        <span class="nxl-mtext">System</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @canany(['user.view_all_branches','user.view_branch','user.create','user.edit'])<li class="nxl-item"><a class="nxl-link" href="{{ route('users.index') }}">Users</a></li>@endcanany
                        @can('role.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('roles.index') }}">Roles</a></li>@endcan
                        @can('permission.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('permissions.index') }}">Permissions</a></li>@endcan
                        @canany(['branch.view_all','branch.manage'])<li class="nxl-item"><a class="nxl-link" href="{{ route('branches.index') }}">Branches</a></li>@endcanany
                        @can('database.backup.download')<li class="nxl-item"><a class="nxl-link" href="{{ route('database.backup.download') }}"><i class="feather-download-cloud me-1"></i> Database Backup</a></li>@endcan
                    </ul>
                </li>
                @endcanany

                @canany(['business.manage','platform.manage','status_stage.manage'])
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">Business</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('settings.index') }}">Settings</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('settings.seoindex') }}">Seo</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('settings.socialindex') }}">Social</a></li>@endcan
                        @can('platform.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('platforms.index') }}">Platform</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('departments.index') }}">Department</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('designations.index') }}">Designation</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('vehicles.index') }}">Vehicle</a></li>@endcan
                        @can('business.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('expense-types.index') }}">DA / Expense Types</a></li>@endcan
                        @can('status_stage.manage')<li class="nxl-item"><a class="nxl-link" href="{{ route('status_stages.index') }}">Status Stage</a></li>@endcan
                    </ul>
                </li>
                @endcanany
            </ul>
        </div>
    </div>
</nav>
