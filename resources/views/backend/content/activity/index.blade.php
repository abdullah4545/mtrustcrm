@extends('backend.master')

@section('title')
{{ ($business?->business_name ?? 'Medi Trust Solution') }} - Activities
@endsection

@section('maincontent') 

@php
    $activitySavedFilters = session('crm_filter_sessions.'.md5(trim(request()->path(), '/')), []);
    $af = fn($id, $default = '') => $activitySavedFilters['id:'.$id] ?? $default;
@endphp


    <div class="nxl-content"> 
        {{-- HEADER --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Activity</h5>
                </div>

                <div class="d-none d-lg-block">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item">Activity</li>
                    </ul>
                </div>
            </div>

            <a href="{{route('activities.quick.create')}}" class="btn btn-primary">
                <i class="feather-plus"></i> Add Activity
            </a>
        </div>

    </div>

    <div class="main-content activity-list-page">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3 activity-filters">
                    @if($showStaffColumn && auth()->user()->can('staff.filter'))
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Staff / Created By</label>
                        <select id="f_created_by" class="form-control">
                            <option value="">All Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" @selected((string)$af('f_created_by') === (string)$staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">From Date</label>
                        <input type="date" id="f_from_date" class="form-control" value="{{ $af('f_from_date') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">To Date</label>
                        <input type="date" id="f_to_date" class="form-control" value="{{ $af('f_to_date') }}">
                    </div>
                    @if($showStatus)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Status</label>
                        <select id="f_status" class="form-control">
                            <option value="pending" @selected($af('f_status') === 'pending')>Pending</option>
                            <option value="rejected" @selected($af('f_status') === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-sm-6 col-lg-3"><label class="form-label">Payment Status</label><select id="f_payment_status" class="form-control"><option value="">All Payment Status</option><option value="unpaid" @selected($af('f_payment_status') === 'unpaid')>Unpaid</option><option value="waiting_for_payment" @selected($af('f_payment_status') === 'waiting_for_payment')>Waiting for Payment</option></select></div>
                    <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-end">
                        <button type="button" id="f_date_reset" class="btn btn-light w-100">Reset Filter</button>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                        <button type="button" id="activityPdf" class="btn btn-danger btn-sm"><i class="feather-file-text"></i> PDF</button>
                        <button type="button" id="activityExcel" class="btn btn-success btn-sm"><i class="feather-file"></i> Excel</button>
                        <button type="button" id="activityPrint" class="btn btn-secondary btn-sm"><i class="feather-printer"></i> Print</button>
                    </div>
                </div>

                <div id="activitySummary" class="alert alert-light border d-flex flex-wrap gap-4 mb-3"><strong>Activities: <span data-summary="activity_count">0</span></strong><span>TA Payable: <b data-summary="total_ta">0</b></span><span>DA Hotel and Others: <b data-summary="total_da">0</b></span><strong>Total Payable: <span data-summary="grand_total">0</span></strong></div>
                @if($canBulkReview || $canManagePayment)<div class="d-flex flex-wrap gap-2 mb-3 align-items-center activity-bulk-actions"><strong>Bulk Action:</strong>@if($canBulkReview)<button class="btn btn-sm btn-success bulk-action" data-action="approve">Approve</button><button class="btn btn-sm btn-danger bulk-action" data-action="reject">Reject</button>@endif @if($canManagePayment)<button class="btn btn-sm btn-secondary bulk-action" data-action="unpaid">Mark Unpaid</button><button class="btn btn-sm btn-warning bulk-action" data-action="waiting_for_payment">Waiting for Payment</button><button class="btn btn-sm btn-primary bulk-action" data-action="paid">Mark Paid</button>@endif <span id="selectedCount" class="text-muted small">0 selected</span></div>@endif

                <p class="small text-muted d-md-none mb-2">Swipe left or right inside the table to view all columns.</p>
                <table class="table table-bordered align-middle" id="activityTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="form-check-input"></th><th>#</th>
                        <th>Created</th><th>Organization / Contact</th><th>From &rarr; To</th><th>Amount</th><th>{{ $showStatus ? 'Status / Payment' : 'Payment Status' }}</th>
                        <th width="150">Action</th>
                    </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>


<style>
.activity-list-page .card-body{min-width:0}
#activityTable_wrapper{display:block!important;width:100%;max-width:100%;overflow:visible!important}
#activityTable_wrapper::after{content:"";display:block;clear:both}
#activityTable_wrapper>.row{display:flex;flex-wrap:wrap;margin-top:0}
#activityTable_wrapper .activity-table-scroll{display:block;clear:both;width:100%;max-width:100%;height:auto!important;max-height:none!important;overflow-x:auto!important;margin:0;border:1px solid #e5e7eb;border-radius:8px}
#activityTable{width:100%!important;min-width:1480px;margin:0!important;border-collapse:collapse!important;table-layout:auto!important}
#activityTable th{white-space:nowrap;background:#f8fafc;font-size:12px;padding:12px 10px;vertical-align:middle}
#activityTable td{vertical-align:top;padding:10px;font-size:13px;line-height:1.5;height:auto}
#activityTable .activity-date{min-width:145px;white-space:nowrap}
#activityTable .activity-staff{min-width:130px;max-width:180px;white-space:normal;overflow-wrap:anywhere}
#activityTable .activity-organization{min-width:180px;max-width:240px;white-space:normal;overflow-wrap:anywhere}
#activityTable td.activity-location{min-width:310px!important;max-width:none!important;white-space:nowrap!important;overflow-wrap:normal!important}
#activityTable td.activity-location .badge{white-space:nowrap!important}
#activityTable .activity-money{white-space:nowrap;min-width:80px}
#activityTable .activity-actions-cell{min-width:270px;width:270px}
#activityTable .activity-actions{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;align-items:center;gap:6px;min-width:250px;max-width:280px}
#activityTable .activity-actions .btn{min-height:32px;padding:6px 10px;line-height:1.2;font-size:11px;margin:0;flex:0 0 auto;width:auto;white-space:nowrap}
#activityTable .activity-actions .badge{align-self:center;white-space:nowrap}
#activityTable_wrapper .dataTables_length,#activityTable_wrapper .dataTables_filter,#activityTable_wrapper .dataTables_info,#activityTable_wrapper .dataTables_paginate{float:none!important;clear:none!important;margin:0;padding-top:0}
#activityTable_wrapper .dataTables_filter{text-align:right}
#activityTable_wrapper .dataTables_filter label{display:flex;align-items:center;justify-content:flex-end;gap:8px;margin:0}
#activityTable_wrapper .dataTables_filter input{width:200px!important;max-width:100%;margin:0}
#activityTable_wrapper .dataTables_length label{display:flex;align-items:center;gap:8px;margin:0}
#activityTable_wrapper .dataTables_length select{width:auto!important;min-width:72px}
#activityTable_wrapper .dataTables_paginate{text-align:right}
.activity-filters .form-label{margin-bottom:6px}
@media(max-width:767.98px){
 .activity-list-page{padding:12px!important}
 .activity-list-page .card-body{padding:12px!important}
 .activity-list-page .activity-filters{margin-bottom:16px!important}
 .activity-list-page .activity-bulk-actions{padding:10px;background:#f8fafc;border-radius:8px}
 .activity-list-page .activity-bulk-actions>strong{flex-basis:100%}
 .activity-list-page .activity-bulk-actions .btn{min-height:40px}
 #activityTable_wrapper .activity-table-tools>div,#activityTable_wrapper .activity-table-footer>div{min-width:0}
 #activityTable_wrapper .dataTables_filter,#activityTable_wrapper .dataTables_info,#activityTable_wrapper .dataTables_length{text-align:left}
 #activityTable_wrapper .dataTables_filter label{justify-content:flex-start}
 #activityTable_wrapper .dataTables_filter input{width:100%!important;min-width:0;flex:1}
 #activityTable_wrapper .dataTables_paginate{display:flex;flex-wrap:wrap;justify-content:flex-start;white-space:normal}
 #activityTable_wrapper .dataTables_paginate .paginate_button{padding:6px 10px;min-height:36px;margin:2px}
 #activityTable{min-width:1480px}
}
</style>

@endsection


@push('modals')
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle">Add Activity</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="activityForm" method="POST">
                    @csrf
                    <input type="hidden" id="activity_id">

                    <div class="row g-2">

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Date</label>
                            <input type="date" id="date" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Organization</label>
                            <select id="organization_id" class="form-control" required>
                                <option value="">Select Organization</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Department</label>
                            <select id="department" class="form-control">
                                <option value="">Select Department</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Contact Person</label>
                            <input type="text" id="contact_person" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>From</label>
                            <input type="text" id="from_location" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>To</label>
                            <input type="text" id="to_location" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Vehicle</label>
                            <input type="text" id="vehicle" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label>Work Details</label>
                            <textarea id="work_details" class="form-control"></textarea>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>TA</label>
                            <input type="number" id="ta" class="form-control" value="0">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>DA</label>
                            <input type="number" id="da" class="form-control" value="0">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label>Total</label>
                            <input type="number" id="total" class="form-control" readonly>
                        </div>

                        <div class="col-md-12">
                            <label>Remarks</label>
                            <textarea id="remarks" class="form-control"></textarea>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnSave">Save</button>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table, modal;

    const ROUTE_DATATABLE = "{{ route('activities.datatable') }}";
    const ROUTE_STORE = "{{ route('activities.store') }}";
    const ROUTE_UPDATE = "{{ url('activities') }}";
    const ROUTE_DELETE = "{{ url('activities') }}";
    const ROUTE_ORG = "{{ url('activities/ajax/organizations') }}";
    const ROUTE_DEP = "{{ url('activities/ajax/departments') }}";

    function loadDropdowns(){
        if($.fn.select2){
            $('#organization_id').select2({width:'100%',placeholder:'Search organization...',dropdownParent:$('#organization_id').closest('.modal').length?$('#organization_id').closest('.modal').find('.modal-content').first():$(document.body),ajax:{url:ROUTE_ORG,dataType:'json',delay:300,data:p=>({q:p.term||'',page:p.page||1}),processResults:r=>r,cache:true}});
        }

        // department
        $.get(ROUTE_DEP, function(res){
            let html = `<option value="">Select Department</option>`;
            res.forEach(v=>{
                html += `<option value="${v.title}">${v.title}</option>`;
            });
            $('#department').html(html);
        });
    }

    $(document).ready(function(){

   

        table = $('#activityTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 50, 100, -1], [10, 50, 100, 'All']],
            processing:true,
            serverSide:true,
            autoWidth:false,
            dom:'<"row g-2 align-items-center mb-3 activity-table-tools"<"col-12 col-sm-6"l><"col-12 col-sm-6"f>><"table-responsive activity-table-scroll"t><"row g-2 align-items-center mt-3 activity-table-footer"<"col-12 col-md-5"i><"col-12 col-md-7"p>>',
            stateSave:true,
            stateDuration:-1,
            stateSaveParams:function(settings,data){data.crmListLayout='merged-v6-status-payment';},
            stateLoadParams:function(settings,data){return data.crmListLayout==='merged-v6-status-payment';},
            order:[[2,'desc']],
            ajax:{url:ROUTE_DATATABLE,dataSrc:function(json){Object.entries(json.summary||{}).forEach(([key,value])=>$('#activitySummary [data-summary="'+key+'"]').text(Number(value).toLocaleString('en-US',{maximumFractionDigits:2})));return json.data;},data:function(d){d.created_by=$('#f_created_by').val();d.from_date=$('#f_from_date').val();d.to_date=$('#f_to_date').val();d.status=@json($showStatus) && $('#f_status').val()==='rejected'?'rejected':'pending';d.payment_status=['unpaid','waiting_for_payment'].includes($('#f_payment_status').val())?$('#f_payment_status').val():'';}},
            columns:[
                {data:'select',orderable:false,searchable:false},
                {data:'DT_RowIndex',orderable:false,searchable:false},
                {data:'created_display',name:'activity_at',className:'activity-date',searchable:false},
                {data:'organization_contact',name:'organization_name',className:'activity-organization'},
                {data:'route_display',className:'activity-location',orderable:false,searchable:false},
                {data:'amount_display',className:'activity-money',orderable:false,searchable:false},
                {data:'payment_status',className:'activity-status-cell',orderable:false,searchable:false,render:function(data,type,row){
                    if(type!=='display') return data;
                    return '<div class="d-flex flex-column align-items-start gap-1">'+(@json($showStatus)?'<div>'+row.status+'</div>':'')+'<div>'+data+'</div></div>';
                }},
                {data:'action',className:'activity-actions-cell',orderable:false,searchable:false},
            ]
        });

        

        if(!['pending','rejected'].includes($('#f_status').val())) $('#f_status').val('pending');
        $('#f_created_by,#f_from_date,#f_to_date,@if($showStatus)#f_status,@endif #f_payment_status').on('change',function(){ if(window.CrmPersistentFilters) window.CrmPersistentFilters.saveNow(); table.ajax.reload(null, false); });
        $('#f_date_reset').on('click',function(){
            $('#f_created_by,#f_from_date,#f_to_date,#f_payment_status').val('').trigger('change.select2');
            $('#f_status').val('pending').trigger('change.select2');
            if (window.CrmPersistentFilters) window.CrmPersistentFilters.clearCurrentPage();
            table.ajax.reload();
        });

        function activityExportParams(){
            const p = new URLSearchParams();
            const values = {
                created_by: $('#f_created_by').val() || '',
                from_date: $('#f_from_date').val() || '',
                to_date: $('#f_to_date').val() || '',
                status: @json($showStatus) ? ($('#f_status').val() || '') : '',
                payment_status: $('#f_payment_status').val() || ''
            };
            Object.entries(values).forEach(([k,v]) => { if(v) p.append(k,v); });
            return p.toString();
        }
        $('#activityPdf').on('click',()=>window.open(@json(route('activities.report.pdf'))+'?'+activityExportParams(),'_blank'));
        $('#activityExcel').on('click',()=>window.location.href=@json(route('activities.report.excel'))+'?'+activityExportParams());
        $('#activityPrint').on('click',()=>window.open(@json(route('activities.report.print'))+'?'+activityExportParams(),'_blank'));

        $('#ta,#da').on('keyup change', function(){
            let ta = parseFloat($('#ta').val()) || 0;
            let da = parseFloat($('#da').val()) || 0;
            $('#total').val(ta + da);
        });
  

        $(document).on('click','.btn-delete',function(){
            let id = $(this).data('id');

            if(confirm('Delete?')){
                $.post(ROUTE_DELETE+'/'+id+'/delete',{},function(){
                    table.ajax.reload(null,false);
                });
            }
        });


        function updateSelected(){ $('#selectedCount').text($('.activity-check:checked').length+' selected'); }
        $('#selectAll').on('change',function(){ $('.activity-check').prop('checked',this.checked); updateSelected(); });
        $(document).on('change','.activity-check',updateSelected);
        table.on('draw',function(){ $('#selectAll').prop('checked',false); updateSelected(); });
        $(document).on('click','.bulk-action',function(){
            const ids=$('.activity-check:checked').map(function(){return Number(this.value)}).get(), action=$(this).data('action');
            if(!ids.length){Swal.fire('Select Activity','Please select at least one activity.','warning');return;}
            Swal.fire({input:action==='reject'?'textarea':undefined,inputLabel:action==='reject'?'Rejection reason for the selected activities':undefined,inputValidator:value=>action==='reject'&&!String(value||'').trim()?'Please enter a rejection reason.':undefined,title:'Apply bulk action?',text:`${ids.length} activity(s): ${String(action).replaceAll('_',' ')}`,icon:'question',showCancelButton:true,confirmButtonText:'Yes, continue'}).then(r=>{if(!r.isConfirmed)return; $.post(@json(route('activities.bulk-action')),{ids,action,review_note:action==='reject'?r.value:''}).done(x=>{Swal.fire('Done',x.message,'success');table.ajax.reload(null,false)}).fail(x=>{let m=x.responseJSON?.message||'Unable to update';if(x.status===422&&x.responseJSON?.errors)m=Object.values(x.responseJSON.errors)[0][0];Swal.fire('Blocked',m,'error')});});
        });

        $(document).on('click','.btn-review',function(){
            const id=$(this).data('id'), status=$(this).data('status');
            Swal.fire({title:status==='approved'?'Approve activity?':'Reject activity?',input:'text',inputLabel:status==='rejected'?'Rejection reason (required)':'Review note (optional)',inputValidator:value=>status==='rejected'&&!String(value||'').trim()?'Please enter a rejection reason.':undefined,showCancelButton:true,confirmButtonText:status==='approved'?'Approve':'Reject'}).then(r=>{
                if(!r.isConfirmed) return;
                $.post(ROUTE_UPDATE+'/'+id+'/review',{status:status,review_note:r.value||''}).done(x=>{Swal.fire('Done',x.message,'success');table.ajax.reload(null,false)}).fail(x=>Swal.fire('Error',x.responseJSON?.message||'Unable to review','error'));
            });
        });

    });

    function clearForm(){
        $('#activityForm')[0].reset();
        $('#activity_id').val('');
        $('#total').val(0);
    }
</script>
@endpush
@push('scripts')
@include('backend.partials.crm-list-design')
@endpush
