@extends('backend.master')
@section('title', ($business?->business_name ?? 'Medi Trust Solution').' - '.($mode==='tour'?'Tour Report':'Activity Sort List'))
@section('maincontent')
@php
    $saved = session('crm_filter_sessions.'.md5(trim(request()->path(), '/')), []);
    $rf = function ($id, $default = '') use ($saved) {
        return $saved['id:'.$id] ?? $default;
    };
    $isTour = ($mode === 'tour');
    $reportUrls = $isTour ? [
        'data' => route('activities.tour-report.data'),
        'pdf' => route('activities.tour-report.pdf'),
        'print' => route('activities.tour-report.print'),
        'excel' => route('activities.tour-report.excel'),
    ] : [
        'data' => route('activities.sort-list.data'),
        'pdf' => route('activities.sort-list.pdf'),
        'print' => route('activities.sort-list.print'),
        'excel' => route('activities.sort-list.excel'),
    ];
@endphp
<div class="nxl-content"><div class="page-header"><div class="page-header-left"><div class="page-header-title"><h5 class="m-b-10">{{ $isTour?'Tour Report':'Activity Sort List' }}</h5></div></div></div>
<div class="main-content">
<div class="card"><div class="card-header"><h5 class="mb-0">Report Filters</h5></div><div class="card-body"><form id="reportFilterForm" data-persist-filters><div class="row g-3">
@if($isTour)
@can('staff.filter')<div class="col-md-4"><label class="form-label">Staff</label><select id="created_by" name="created_by" class="form-control"><option value="">All Staff</option>@foreach($users as $u)<option value="{{$u->id}}" @selected((string)$rf('created_by')===(string)$u->id)>{{$u->name}}</option>@endforeach</select></div>@endcan
<div class="col-md-3"><label class="form-label">From Date</label><input type="date" id="from_date" name="from_date" class="form-control" value="{{$rf('from_date',now()->startOfMonth()->format('Y-m-d'))}}"></div>
<div class="col-md-3"><label class="form-label">To Date</label><input type="date" id="to_date" name="to_date" class="form-control" value="{{$rf('to_date',now()->format('Y-m-d'))}}"></div>
@else
@can('staff.filter')<div class="col-md-3"><label class="form-label">Created By / User</label><select id="created_by" name="created_by" class="form-control"><option value="">All Users</option>@foreach($users as $u)<option value="{{$u->id}}" @selected((string)$rf('created_by')===(string)$u->id)>{{$u->name}}</option>@endforeach</select></div>@endcan
<div class="col-md-2"><label class="form-label">From Date</label><input type="date" id="from_date" name="from_date" class="form-control" value="{{$rf('from_date',now()->startOfMonth()->format('Y-m-d'))}}"></div>
<div class="col-md-2"><label class="form-label">To Date</label><input type="date" id="to_date" name="to_date" class="form-control" value="{{$rf('to_date',now()->format('Y-m-d'))}}"></div>
@if($canViewStatus)<div class="col-md-2"><label class="form-label">Status</label><select id="status" name="status" class="form-control"><option value="">All Status</option>@foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $v=>$l)<option value="{{$v}}" @selected($rf('status')===$v)>{{$l}}</option>@endforeach</select></div>@endif
<div class="col-md-3"><label class="form-label">Payment Status</label><select id="payment_status" name="payment_status" class="form-control"><option value="">All Payment Status</option>@foreach(['unpaid'=>'Unpaid','waiting_for_payment'=>'Waiting for Payment','paid'=>'Paid'] as $v=>$l)<option value="{{$v}}" @selected($rf('payment_status')===$v)>{{$l}}</option>@endforeach</select></div>
@endif
<div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Generate Report</button><button class="btn btn-light" type="button" id="btnReset">Reset</button></div>
</div></form></div></div>
<div class="row g-3 mb-3"><div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">{{ $isTour?'Total Tours':'Total Activities' }}</small><h3 id="count">0</h3></div></div></div><div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Total TA</small><h3 id="ta">0.00</h3></div></div></div><div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Total DA</small><h3 id="da">0.00</h3></div></div></div><div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Grand Total</small><h3 id="total">0.00</h3></div></div></div></div>
<div class="card"><div class="card-header d-flex justify-content-between flex-wrap gap-2"><h5 class="mb-0">{{ $isTour?'Tour Report':'Activity Sort List' }}</h5><div class="d-flex gap-2"><button id="excel" class="btn btn-success">Excel</button><button id="pdf" class="btn btn-danger">PDF</button><button id="print" class="btn btn-dark">Print</button></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered mb-0" style="min-width:1500px"><thead id="thead"></thead><tbody id="tbody"></tbody></table></div></div></div>
</div></div>
@endsection
@push('scripts')
<script>
const urls = {!! json_encode($reportUrls, JSON_UNESCAPED_SLASHES) !!};
const defaultColumns=@json($defaultColumns);
function params(){
    let p = new URLSearchParams();
    const filterIds = @if($isTour) ['created_by','from_date','to_date'] @else ['created_by','from_date','to_date','status','payment_status'] @endif;
    filterIds.forEach(id => {
        let e = document.getElementById(id);
        if (e && e.value) p.append(id, e.value);
    });
    defaultColumns.forEach(c => p.append('columns[]', c));
    return p;
}
async function load(){const r=await fetch(urls.data+'?'+params().toString(),{headers:{Accept:'application/json'}}); if(!r.ok){Swal.fire('Error','Could not load report.','error');return;} const d=await r.json(); document.getElementById('count').textContent=d.summary.count;document.getElementById('ta').textContent=d.summary.ta;document.getElementById('da').textContent=d.summary.da;document.getElementById('total').textContent=d.summary.total;document.getElementById('thead').innerHTML='<tr>'+d.columns.map(c=>`<th>${c.label}</th>`).join('')+'</tr>';document.getElementById('tbody').innerHTML=d.rows.length?d.rows.map(row=>'<tr>'+d.columns.map(c=>`<td>${row[c.key]??''}</td>`).join('')+'</tr>').join(''):`<tr><td colspan="${d.columns.length}" class="text-center py-4">No data found.</td></tr>`;}
$('#reportFilterForm').on('submit',e=>{e.preventDefault();window.CrmPersistentFilters?.saveNow();load();});
$('#btnReset').on('click',async()=>{if(window.CrmPersistentFilters?.clearCurrentPage){ await window.CrmPersistentFilters.clearCurrentPage(); } location.reload();});
['excel','pdf','print'].forEach(k=>document.getElementById(k).addEventListener('click',()=>{let u=urls[k]+'?'+params().toString(); if(k==='print')window.open(u,'_blank');else window.location.href=u;}));
$(function(){ $('#created_by,#status,#payment_status').each(function(){if(this)$(this).trigger('change.select2')}); load(); });
</script>
@endpush
