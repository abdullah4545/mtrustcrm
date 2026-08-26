@extends('backend.master')
@section('title','Dashboard')
@section('maincontent')
<style>
.crm-dashboard{--crm-border:#e8edf4;--crm-text:#17324d;--crm-muted:#7b8798;--crm-soft:#f7f9fc;--mts-blue:#0b73c9;--mts-red:#ef1b2d;--mts-gold:#f4a51c}.crm-dashboard .page-header{border:0;background:transparent;padding-left:0;padding-right:0}.mts-dashboard-head{background:#fff;border:1px solid var(--crm-border);border-radius:14px;overflow:hidden;box-shadow:0 7px 22px rgba(20,50,80,.04);margin-bottom:14px}.mts-dashboard-head-main{min-height:66px;padding:11px 14px;display:flex;align-items:center;justify-content:space-between;gap:16px}.mts-head-mark{width:38px;height:38px;border-radius:11px;background:#edf7ff;color:var(--mts-blue);display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto}.mts-dashboard-head h5{font-size:17px;font-weight:780;color:var(--crm-text);letter-spacing:-.3px}.mts-dashboard-head small{font-size:11px;color:#8794a4}.mts-head-dot{width:4px;height:4px;border-radius:50%;background:#c5d1dc}.mts-head-sub{font-size:11px;color:#8794a4;margin-top:3px}.mts-branch-wrap{min-width:210px;height:40px;border:1px solid #e1eaf3;border-radius:11px;display:flex;align-items:center;gap:7px;padding-left:11px;background:#f9fbfd;color:#718399}.mts-branch-wrap .form-select{border:0;background-color:transparent;box-shadow:none!important;padding-left:2px;height:38px;min-height:38px;font-size:12px;font-weight:650;color:#40566d}.mts-head-rule{height:3px;background:linear-gradient(90deg,var(--mts-blue) 0 58%,var(--mts-red) 58% 90%,var(--mts-gold) 90%)}.crm-page-head h5{font-size:20px;font-weight:750;letter-spacing:-.35px}.crm-branch-filter{min-width:180px;border-radius:10px;height:40px}.crm-topbar{border:1px solid var(--crm-border);border-radius:16px;background:#fff;box-shadow:0 8px 26px rgba(15,23,42,.045);overflow:hidden}.crm-topbar-main{padding:16px 18px}.crm-topbar-title{font-size:17px;font-weight:750;color:var(--crm-text);margin:0}.crm-topbar-sub{font-size:12px;color:var(--crm-muted);margin-top:2px}.crm-top-actions{display:flex;gap:8px;flex-wrap:wrap}.crm-action-chip{height:38px;padding:0 13px;border-radius:10px;border:1px solid var(--crm-border);background:#fff;color:#334155;text-decoration:none;display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:650;transition:.18s}.crm-action-chip:hover{background:#f8fafc;border-color:#cbd5e1;color:#1d4ed8}.crm-action-chip.primary{background:#0b73c9;color:#fff;border-color:#0b73c9}.crm-action-chip.primary:hover{background:#075b9f;color:#fff}.crm-mini-kpis{display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--crm-border)}.crm-mini-kpi{padding:14px 18px;border-right:1px solid var(--crm-border);min-width:0}.crm-mini-kpi:last-child{border-right:0}.crm-mini-label{font-size:11px;color:var(--crm-muted);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.crm-mini-value{font-size:21px;font-weight:780;color:var(--crm-text);line-height:1.2;margin-top:5px;letter-spacing:-.45px}.crm-mini-value.danger{color:#dc2626}.crm-kpi{border:1px solid var(--crm-border);border-radius:14px;box-shadow:0 5px 18px rgba(15,23,42,.035);height:100%;background:#fff}.crm-kpi .card-body{padding:15px 16px}.crm-kpi .kpi-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:#f3f6fa;font-size:17px}.crm-kpi .kpi-number{font-size:21px;font-weight:760;color:var(--crm-text);letter-spacing:-.45px}.crm-section-card{border:1px solid var(--crm-border);border-radius:15px;box-shadow:0 5px 20px rgba(15,23,42,.035);overflow:hidden}.crm-section-card .card-header{background:#fff;border-bottom:1px solid var(--crm-border);padding:13px 16px}.crm-section-card .card-body{padding:14px 16px}.crm-section-card h6{font-size:14px}.crm-list-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f0f2f5}.crm-list-row:last-child{border-bottom:0}.crm-list-avatar{width:36px;height:36px;border-radius:10px;background:#eaf5ff;color:#0b73c9;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex:0 0 auto}.crm-list-main{min-width:0;flex:1}.crm-list-main strong,.crm-list-main span{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.crm-list-main small{color:#8492a6;font-size:11px}.crm-empty{padding:22px 12px;text-align:center;color:#94a3b8}.territory-card{border:1px solid var(--crm-border);border-radius:14px;background:#fff}.territory-card .territory-icon{width:36px;height:36px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569}.crm-stat-pill{border-radius:999px;padding:4px 8px;font-size:10px;font-weight:650;background:#f1f5f9;color:#475569}.crm-stat-pill.success{background:#ecfdf5;color:#047857}.crm-compact-shortcuts{display:flex;gap:8px;flex-wrap:wrap}.crm-shortcut{display:inline-flex;align-items:center;gap:6px;padding:8px 10px;border:1px solid var(--crm-border);border-radius:10px;text-decoration:none;color:#475569;background:#fff;font-size:11px;font-weight:650}.crm-shortcut:hover{background:#f8fafc;color:#1d4ed8}.crm-shortcut i{font-size:14px}
@media(max-width:991.98px){.crm-mini-kpis{grid-template-columns:repeat(2,1fr)}.crm-mini-kpi:nth-child(2){border-right:0}.crm-mini-kpi:nth-child(-n+2){border-bottom:1px solid var(--crm-border)}}
@media(max-width:767.98px){.mts-dashboard-head{margin:0 12px 12px}.mts-dashboard-head-main{align-items:stretch;flex-direction:column;padding:12px}.mts-branch-wrap{width:100%;min-width:0}.crm-dashboard .main-content{padding-left:12px;padding-right:12px}.crm-dashboard .page-header{padding-left:12px;padding-right:12px}.crm-page-head{align-items:flex-start!important;flex-direction:column}.crm-branch-filter{width:100%;min-width:0}.crm-topbar{border-radius:14px}.crm-topbar-main{padding:14px}.crm-topbar-title{font-size:15px}.crm-top-actions{width:100%;display:grid;grid-template-columns:repeat(2,1fr)}.crm-action-chip{justify-content:center;padding:0 9px}.crm-mini-kpi{padding:12px 14px}.crm-mini-value{font-size:19px}.crm-kpi .card-body{padding:13px}.crm-kpi .kpi-number{font-size:19px}.crm-section-card .card-header{padding:12px 14px}.crm-section-card .card-body{padding:12px 14px}}
</style>
<div class="crm-dashboard">
    <div class="nxl-content">
        <div class="mts-dashboard-head">
            <div class="mts-dashboard-head-main">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="mts-head-mark"><i class="feather-grid"></i></span>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap"><h5 class="mb-0">Executive Dashboard</h5><span class="mts-head-dot"></span><small>{{ now()->format('l, d M Y') }}</small></div>
                        <div class="mts-head-sub">{{ $business?->business_name ?? 'Medi Trust Solution' }} · CRM Operations</div>
                    </div>
                </div>
                @if(count($branches))
                    <div class="mts-branch-wrap"><i class="feather-git-branch"></i><select id="branch_filter" class="form-select crm-branch-filter"><option value="">All Branches</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->branch_name }}</option>@endforeach</select></div>
                @endif
            </div>
            <div class="mts-head-rule"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="crm-topbar mb-3">
            <div class="crm-topbar-main d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div>
                    <h2 class="crm-topbar-title">{{ now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening') }}, {{ auth()->user()->name }}</h2>
                    <div class="crm-topbar-sub">{{ now()->format('D, d M Y') }} · Your CRM overview</div>
                </div>
                <div class="crm-top-actions">
                    @can('activity.create')<a href="{{ route('activities.quick.create') }}" class="crm-action-chip primary"><i class="feather-plus"></i> Activity</a>@endcan
                    @can('lead.create')<a href="{{ route('leads.quickCreate') }}" class="crm-action-chip"><i class="feather-user-plus"></i> Lead</a>@endcan
                    @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])<a href="{{ route('followups.index') }}" class="crm-action-chip"><i class="feather-phone-call"></i> Follow-up</a>@endcanany
                    @can('quotation.create')<a href="{{ route('quotations.create') }}" class="crm-action-chip"><i class="feather-file-text"></i> Quotation</a>@endcan
                </div>
            </div>
            <div class="crm-mini-kpis">
                @can('activity.create')
                <div class="crm-mini-kpi"><div class="crm-mini-label">Activities Today</div><div class="crm-mini-value" id="hero_activity_today">0</div></div>
                @endcan
                @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])
                <div class="crm-mini-kpi"><div class="crm-mini-label">Follow-ups Today</div><div class="crm-mini-value" id="hero_followups_today">0</div></div>
                <div class="crm-mini-kpi"><div class="crm-mini-label">Overdue Follow-ups</div><div class="crm-mini-value danger" id="hero_followups_overdue">0</div></div>
                @endcanany
                @canany(['sale.view_all_branches','sale.view_branch','sale.view_self'])
                <div class="crm-mini-kpi"><div class="crm-mini-label">Sales Today</div><div class="crm-mini-value" id="hero_sales_today">৳0</div></div>
                @endcanany
            </div>
        </div>

        @if(auth()->user()->hasRole('staff'))
        <div class="card territory-card mb-3"><div class="card-body p-3">
            <div class="d-flex align-items-start gap-3"><div class="territory-icon"><i class="feather-map-pin"></i></div><div class="min-w-0"><small class="text-muted">Work Territory</small><div class="d-flex gap-2 flex-wrap mt-1">
                @forelse($territories as $rows)<span class="badge bg-light text-dark border p-2">{{ $rows->first()->district?->name }}: {{ $rows->contains(fn($x)=>is_null($x->upazila_id)) ? 'All Upazilas' : $rows->pluck('upazila.name')->filter()->join(', ') }}</span>@empty<span class="badge bg-danger p-2">No work area assigned</span>@endforelse
            </div></div></div>
        </div></div>
        @endif

        <div class="row g-3 mb-3">
            @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])
            <div class="col-6 col-xl-3"><div class="card crm-kpi"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><small class="text-muted">Open Leads</small><div class="kpi-number mt-1" id="leads_open">0</div></div><div class="kpi-icon"><i class="feather-target"></i></div></div></div></div></div>
            @endcanany
            @can('org.view')
            <div class="col-6 col-xl-3"><div class="card crm-kpi"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><small class="text-muted">Organizations</small><div class="kpi-number mt-1" id="organizations">0</div></div><div class="kpi-icon"><i class="feather-briefcase"></i></div></div></div></div></div>
            @endcan
            @canany(['sale.view_all_branches','sale.view_branch','sale.view_self'])
            <div class="col-6 col-xl-3"><div class="card crm-kpi"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><small class="text-muted">Month Sales</small><div class="kpi-number mt-1 fs-6" id="sales_month">৳0</div></div><div class="kpi-icon"><i class="feather-trending-up"></i></div></div></div></div></div>
            <div class="col-6 col-xl-3"><div class="card crm-kpi"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><small class="text-muted">Total Due</small><div class="kpi-number mt-1 fs-6 text-danger" id="due_total">৳0</div></div><div class="kpi-icon"><i class="feather-alert-circle"></i></div></div></div></div></div>
            @endcanany
        </div>

        <div class="crm-compact-shortcuts mb-3">
            @can('sale.create')<a class="crm-shortcut" href="{{ route('sales.create') }}"><i class="feather-shopping-cart"></i> New Sale</a>@endcan
            @can('org.view')<a class="crm-shortcut" href="{{ route('org.manage.index') }}"><i class="feather-briefcase"></i> Organizations</a>@endcan
            @can('product.view')<a class="crm-shortcut" href="{{ route('products.index') }}"><i class="feather-box"></i> Products</a>@endcan
            @canany(['user.view_all_branches','user.view_branch'])<a class="crm-shortcut" href="{{ route('users.index') }}"><i class="feather-users"></i> Team</a>@endcanany
            @canany(['sale.view_all_branches','sale.view_branch'])<a class="crm-shortcut" href="{{ route('reports.sales') }}"><i class="feather-bar-chart-2"></i> Reports</a>@endcanany
            @can('database.backup.download')<a class="crm-shortcut" href="{{ route('database.backup.download') }}"><i class="feather-download-cloud"></i> Backup</a>@endcan
        </div>

        <div class="row g-3 mb-3">
            @canany(['lead.view_all_branches','lead.view_branch','lead.view_self'])
            <div class="col-xl-7"><div class="card crm-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><div><h6 class="mb-1 fw-bold">Follow-up Queue</h6><small class="text-muted">Today and next 7 days</small></div><a href="{{ route('followups.index') }}" class="btn btn-sm btn-light">View all</a></div>
                <div class="card-body pt-1" id="follow_list"><div class="crm-empty">Loading...</div></div>
            </div></div>
            <div class="col-xl-5"><div class="card crm-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><div><h6 class="mb-1 fw-bold">Latest Leads</h6><small class="text-muted">Recently added opportunities</small></div><a href="{{ route('leads.index') }}" class="btn btn-sm btn-light">View all</a></div>
                <div class="card-body pt-1" id="latest_leads"><div class="crm-empty">Loading...</div></div>
            </div></div>
            @endcanany
        </div>

        @canany(['sale.view_all_branches','sale.view_branch','sale.view_self'])
        <div class="card crm-section-card mb-3"><div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2"><div><h6 class="mb-1 fw-bold">Sales Performance</h6><small class="text-muted">Revenue trend</small></div><div class="d-flex align-items-center gap-2"><span class="crm-stat-pill success">Collected today: <b id="collection_today">৳0</b></span><select id="chart_filter" class="form-control" style="max-width:145px;border-radius:10px"><option value="date">7 Days</option><option value="month" selected>30 Days</option><option value="year">This Year</option></select></div></div><div class="card-body"><div id="salesChart"></div></div></div>
        @endcanany
    </div>
</div>
<script>
let dashboardChart; const money=n=>'৳'+Number(n||0).toLocaleString(undefined,{maximumFractionDigits:2});
const htmlEsc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
function setText(id,value){const el=document.getElementById(id);if(el)el.textContent=value;}
function loadDashboard(){
    const cf=document.getElementById('chart_filter'); const p=new URLSearchParams({chart_filter:cf?cf.value:'month'}); const bf=document.getElementById('branch_filter');if(bf&&bf.value)p.set('branch_id',bf.value);
    fetch("{{ route('dashboard.data') }}?"+p,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(res=>{
        const k=res.kpi||{}; setText('hero_activity_today',k.activity_today??0);setText('hero_followups_today',k.followups_today??0);setText('hero_followups_overdue',k.followups_overdue??0);setText('hero_sales_today',money(k.sales_today));setText('leads_open',k.leads_open??0);setText('organizations',k.organizations??0);setText('sales_month',money(k.sales_month));setText('due_total',money(k.due_total));setText('collection_today',money(k.collection_today));
        const follow=document.getElementById('follow_list');if(follow){const rows=res.lists?.upcoming_followups||[];follow.innerHTML=rows.length?rows.map(f=>{const d=new Date(f.next_followup_at);return `<div class="crm-list-row"><div class="crm-list-avatar"><i class="feather-phone-call"></i></div><div class="crm-list-main"><strong>${htmlEsc(f.person_name||f.lead_no||'Lead')}</strong><span class="text-muted fs-12">${htmlEsc(f.person_phone||'-')}</span><small>${htmlEsc(f.next_action_type||'Follow-up')} · ${d.toLocaleString()}</small></div><a class="btn btn-sm btn-light" href="{{ route('followups.index') }}?q=${encodeURIComponent(f.person_phone||f.lead_no||'')}"><i class="feather-chevron-right"></i></a></div>`}).join(''):'<div class="crm-empty"><i class="feather-check-circle fs-3 d-block mb-2"></i>No upcoming follow-ups</div>'}
        const latest=document.getElementById('latest_leads');if(latest){const rows=res.lists?.latest_leads||[];latest.innerHTML=rows.length?rows.map(l=>`<a href="{{ url('/leads') }}/${l.id}" class="crm-list-row text-decoration-none"><div class="crm-list-avatar">${htmlEsc((l.person_name||'L').charAt(0).toUpperCase())}</div><div class="crm-list-main"><strong class="text-dark">${htmlEsc(l.person_name||l.lead_no)}</strong><span class="text-muted fs-12">${htmlEsc(l.person_phone||l.lead_no||'')}</span></div><span class="crm-stat-pill ${l.lead_state==='closed'?'success':''}">${htmlEsc(l.lead_state||'open')}</span></a>`).join(''):'<div class="crm-empty">No leads yet</div>'}
        if(document.querySelector('#salesChart'))renderChart(res.charts?.sales?.labels||[],res.charts?.sales?.data||[]);
    }).catch(()=>{});
}
function renderChart(labels,data){if(dashboardChart)dashboardChart.destroy();dashboardChart=new ApexCharts(document.querySelector('#salesChart'),{chart:{type:'area',height:240,toolbar:{show:false},zoom:{enabled:false}},series:[{name:'Sales',data}],xaxis:{categories:labels,labels:{rotate:-35}},stroke:{curve:'smooth',width:3},fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:.30,opacityTo:.04,stops:[0,90,100]}},dataLabels:{enabled:false},grid:{borderColor:'#eef2f7'},yaxis:{labels:{formatter:v=>'৳'+Number(v).toLocaleString()}},tooltip:{y:{formatter:v=>money(v)}}});dashboardChart.render();}
document.getElementById('chart_filter')?.addEventListener('change',loadDashboard);document.getElementById('branch_filter')?.addEventListener('change',loadDashboard);loadDashboard();
</script>
@endsection
