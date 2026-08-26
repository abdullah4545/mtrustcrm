<header class="nxl-header crm-header">
    <div class="header-wrapper">
        <div class="header-left d-flex align-items-center gap-3">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse" aria-label="Menu">
                <div class="hamburger hamburger--arrowturn"><div class="hamburger-box"><div class="hamburger-inner"></div></div></div>
            </a>
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button" aria-label="Collapse menu"><i class="feather-align-left"></i></a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display:none" aria-label="Expand menu"><i class="feather-arrow-right"></i></a>
            </div>
            <div class="d-none d-lg-block">
                <div class="fw-semibold text-dark">{{ $business?->business_name ?? ($business?->business_name ?? 'Medi Trust Solution') }}</div>
                <small class="text-muted">Enterprise CRM Workspace</small>
            </div>
        </div>

        <div class="header-right ms-auto">
            <div class="d-flex align-items-center gap-1">
                <div class="dropdown nxl-h-item nxl-header-search">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Search CRM">
                        <i class="feather-search"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown crm-search-menu p-0">
                        <div class="p-3 border-bottom">
                            <div class="input-group crm-search-box">
                                <span class="input-group-text bg-transparent border-0"><i class="feather-search"></i></span>
                                <input type="search" id="crmGlobalSearch" class="form-control border-0" autocomplete="off" placeholder="Search CRM...">
                                <button class="btn border-0" type="button" id="crmSearchClear" aria-label="Clear"><i class="feather-x"></i></button>
                            </div>
                        </div>
                        <div id="crmSearchResults" class="crm-search-results">
                            <div class="crm-header-empty"><i class="feather-search"></i><span>Type at least 2 characters</span></div>
                        </div>
                    </div>
                </div>

                <div class="dropdown nxl-h-item">
                    <a class="nxl-head-link me-0 position-relative" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside" aria-label="Notifications">
                        <i class="feather-bell"></i>
                        <span id="crmNotificationBadge" class="badge bg-danger nxl-h-badge d-none">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown crm-notification-menu p-0">
                        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                            <div><h6 class="mb-0 fw-bold">Notifications</h6><small class="text-muted">Items needing attention</small></div>
                            <button type="button" class="btn btn-sm btn-light" id="crmNotificationRefresh" title="Refresh"><i class="feather-refresh-cw"></i></button>
                        </div>
                        <div id="crmNotificationList" class="crm-notification-list">
                            <div class="crm-header-empty"><span class="spinner-border spinner-border-sm"></span><span>Loading</span></div>
                        </div>
                    </div>
                </div>

                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="crm-user-trigger" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <span class="crm-user-avatar">{{ strtoupper(mb_substr(auth()->user()->name ?? 'U',0,1)) }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown crm-user-menu p-0">
                        <div class="p-3 border-bottom d-flex align-items-center gap-3">
                            <span class="crm-user-avatar lg">{{ strtoupper(mb_substr(auth()->user()->name ?? 'U',0,1)) }}</span>
                            <div class="min-w-0">
                                <h6 class="mb-1 text-truncate">{{ auth()->user()->name }}</h6>
                                <div class="text-muted fs-12 text-truncate">{{ auth()->user()->email }}</div>
                                <span class="badge bg-soft-primary text-primary mt-1">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                            </div>
                        </div>
                        <div class="p-2">
                            @can('business.manage')
                            <a href="{{ route('settings.index') }}" class="dropdown-item rounded"><i class="feather-settings me-2"></i>Business Settings</a>
                            @endcan
                            @canany(['user.view_all_branches','user.view_branch'])
                            <a href="{{ route('users.index') }}" class="dropdown-item rounded"><i class="feather-users me-2"></i>Team & Access</a>
                            @endcanany
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="dropdown-item rounded text-danger" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="feather-log-out me-2"></i>Logout</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
.crm-header .header-wrapper{padding-right:18px}.crm-search-menu{width:min(430px,calc(100vw - 24px));border-radius:16px;overflow:hidden}.crm-search-box{background:#f5f7fa;border-radius:12px}.crm-search-box .form-control{box-shadow:none;background:transparent}.crm-search-results,.crm-notification-list{max-height:430px;overflow:auto}.crm-header-empty{min-height:145px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#8492a6;padding:20px;text-align:center}.crm-header-empty i{font-size:24px}.crm-search-item,.crm-notification-item{display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f2f5;text-decoration:none;transition:.15s}.crm-search-item:hover,.crm-notification-item:hover{background:#f8fafc}.crm-search-icon,.crm-notification-icon{width:38px;height:38px;border-radius:11px;background:#eaf5ff;color:#0b73c9;display:flex;align-items:center;justify-content:center;flex:0 0 auto}.crm-search-meta{min-width:0;flex:1}.crm-search-meta strong,.crm-search-meta span{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.crm-search-meta small{color:#8492a6}.crm-notification-menu{width:min(390px,calc(100vw - 24px));border-radius:16px;overflow:hidden}.crm-notification-item .crm-notification-icon.danger{background:#fff1f2;color:#e11d48}.crm-notification-item .crm-notification-icon.warning{background:#fff7ed;color:#ea580c}.crm-notification-item .crm-notification-icon.primary{background:#eff6ff;color:#2563eb}.crm-user-trigger{display:block;padding:6px}.crm-user-avatar{width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,#0b73c9,#ef1b2d);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700}.crm-user-avatar.lg{width:46px;height:46px;border-radius:14px;flex:0 0 auto}.crm-user-menu{width:280px;border-radius:16px;overflow:hidden}.min-w-0{min-width:0}@media(max-width:575.98px){.crm-header .header-wrapper{padding-left:12px;padding-right:8px}.crm-search-menu,.crm-notification-menu{position:fixed!important;left:12px!important;right:12px!important;top:70px!important;width:auto!important;transform:none!important}.crm-user-menu{width:min(280px,calc(100vw - 24px))}}
</style>
<script>
(function(){
    const searchInput=document.getElementById('crmGlobalSearch'), searchResults=document.getElementById('crmSearchResults'), clearBtn=document.getElementById('crmSearchClear');
    let searchTimer=null, searchAbort=null;
    const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    function searchEmpty(text='Type at least 2 characters'){searchResults.innerHTML=`<div class="crm-header-empty"><i class="feather-search"></i><span>${esc(text)}</span></div>`}
    async function runSearch(){
        const q=searchInput.value.trim(); if(q.length<2){searchEmpty();return} if(searchAbort)searchAbort.abort(); searchAbort=new AbortController();
        searchResults.innerHTML='<div class="crm-header-empty"><span class="spinner-border spinner-border-sm"></span><span>Searching</span></div>';
        try{const r=await fetch(`{{ route('header.search') }}?q=${encodeURIComponent(q)}`,{signal:searchAbort.signal,headers:{'Accept':'application/json'}});const d=await r.json();
            if(!d.results?.length){searchEmpty('No matching CRM record found');return}
            searchResults.innerHTML=d.results.map(x=>`<a class="crm-search-item" href="${esc(x.url)}"><span class="crm-search-icon"><i class="${esc(x.icon)}"></i></span><span class="crm-search-meta"><small>${esc(x.type)}</small><strong>${esc(x.title)}</strong><span class="text-muted fs-12">${esc(x.subtitle)}</span></span><i class="feather-chevron-right text-muted align-self-center"></i></a>`).join('');
        }catch(e){if(e.name!=='AbortError')searchEmpty('Search unavailable');}
    }
    searchInput?.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(runSearch,280)}); clearBtn?.addEventListener('click',()=>{searchInput.value='';searchInput.focus();searchEmpty()});

    const list=document.getElementById('crmNotificationList'), badge=document.getElementById('crmNotificationBadge');
    async function loadNotifications(){
        if(!list)return; list.innerHTML='<div class="crm-header-empty"><span class="spinner-border spinner-border-sm"></span><span>Loading</span></div>';
        try{const r=await fetch(`{{ route('header.notifications') }}`,{headers:{'Accept':'application/json'}});const d=await r.json();
            badge.textContent=d.count||0; badge.classList.toggle('d-none',!(d.count>0));
            if(!d.items?.length){list.innerHTML='<div class="crm-header-empty"><i class="feather-check-circle"></i><span>Nothing needs attention</span></div>';return}
            list.innerHTML=d.items.map(x=>`<a class="crm-notification-item" href="${esc(x.url)}"><span class="crm-notification-icon ${esc(x.level)}"><i class="${esc(x.icon)}"></i></span><span class="crm-search-meta"><strong>${esc(x.title)}</strong><span class="text-muted fs-12 text-wrap">${esc(x.text)}</span></span></a>`).join('');
        }catch(e){list.innerHTML='<div class="crm-header-empty"><span>Notifications unavailable</span></div>'}
    }
    document.getElementById('crmNotificationRefresh')?.addEventListener('click',loadNotifications); loadNotifications();
})();
</script>
