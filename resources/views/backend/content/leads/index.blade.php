@extends('backend.master')

@section('title')
{{ ($business?->business_name ?? 'Medi Trust Solution') }} - Leads
@endsection

@section('maincontent')
<div class="nxl-content">

    {{-- ================= HEADER ================= --}}
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Leads</h5>
            </div>

            <div class="d-none d-lg-block">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item">Leads</li>
                </ul>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center">

            <button class="btn btn-outline-secondary"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#filterSidebar">
                <i class="feather-filter"></i>
                <span class="d-none d-lg-block">Filter</span>
            </button>

            <button type="button" class="btn btn-primary" id="btnOpenCreate">
                <i class="feather-plus"></i>
                <span class="d-none d-lg-block">Add Lead</span>
            </button>

        </div>
    </div>

</div>

<div class="main-content">

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body p-2 p-lg-4">

            <div class="table-responsive">
                <table class="table table-bordered" id="leadTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Lead No</th>
                        <th>Organization</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Followup</th>
                        <th>State</th>
                        <th width="160">Action</th>
                    </tr>
                    </thead>
                </table>
            </div>

            {{-- ================= MOBILE CARDS ================= --}}
            <div class="d-block d-md-none mt-3" id="mobileCards"></div>

        </div>
    </div>

</div>

<div class="offcanvas offcanvas-end" id="filterSidebar">
    <div class="offcanvas-header">
        <h5>Filters</h5>
        <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">

        <div class="row g-2">

            <div class="col-12">
                <input type="date" id="f_date_from" class="form-control">
            </div>

            <div class="col-12">
                <input type="date" id="f_date_to" class="form-control">
            </div>

            <div class="col-12">
                <select id="f_status_stage_id" class="form-control">
                    <option value="">All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <input type="text" id="f_search_text" class="form-control" placeholder="Search name/phone">
            </div>

            <div class="col-12">
                <button class="btn btn-light-brand w-100" id="btnReset">Reset</button>
            </div>

        </div>

    </div>
</div>

@endsection

@push('modals')
{{-- Lead Modal --}}
<div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="leadForm">
                    @csrf
                    <input type="hidden" id="lead_id">

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Organization</label>
                            <select id="organization_id" class="form-control">
                                <option value="">-- Select Organization --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <select id="organization_contact_id" class="form-control">
                                <option value="">-- Select Contact --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Person Name</label>
                            <input type="text" id="person_name" class="form-control" placeholder="Auto from contact or type custom">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Person Phone</label>
                            <input type="text" id="person_phone" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Person Email</label>
                            <input type="email" id="person_email" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Source (Platform)</label>
                            <select id="platform_id" class="form-control">
                                <option value="">--</option>
                                @foreach($platforms as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status (StatusStage)</label>
                            <select id="status_stage_id" class="form-control">
                                <option value="">--</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-4">
                            <label class="form-label">Assigned To</label>
                            <select id="assigned_user_id" class="form-control">
                                @foreach($assignees as $user)
                                    <option value="{{ $user->id }}" @selected($user->id === auth()->id())>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lead Progress</label>
                            <select id="lead_state" class="form-control">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Medical Reagents</label>
                            <input type="text" id="subject" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Existing Machine</label>
                            <input type="text" id="existing_machine" class="form-control" placeholder="Existing machine / model">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Note</label>
                            <textarea id="note" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Expected Value</label>
                            <input type="number" step="0.01" id="expected_value" class="form-control" value="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Next Followup</label>
                            <input type="datetime-local" id="next_followup_at" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Next Action</label>
                            <select id="next_action_type" class="form-control">
                                <option value="">--</option>
                                <option value="call">Call</option>
                                <option value="visit">Visit</option>
                                <option value="message">Message</option>
                                <option value="meeting">Meeting</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Lost Reason (only if lost/closed)</label>
                            <input type="text" id="lost_reason" class="form-control">
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light-brand" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnSave">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Activity Modal --}}
<div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="activity_lead_id">

                {{-- Add Activity Form --}}
                <div class="border rounded p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select id="a_activity_type" class="form-control">
                                <option value="call">Call</option>
                                <option value="visit">Visit</option>
                                <option value="message">Message</option>
                                <option value="meeting">Meeting</option>
                                <option value="note">Note</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Outcome</label>
                            <input type="text" id="a_outcome_status" class="form-control" placeholder="interested / no answer ...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Activity Time</label>
                            <input type="datetime-local" id="a_activity_at" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Note</label>
                            <textarea id="a_activity_text" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Next Action</label>
                            <select id="a_next_action_type" class="form-control">
                                <option value="">--</option>
                                <option value="call">Call</option>
                                <option value="visit">Visit</option>
                                <option value="message">Message</option>
                                <option value="meeting">Meeting</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Next Followup</label>
                            <input type="datetime-local" id="a_next_followup_at" class="form-control">
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="btnAddActivity">Add Activity</button>
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div>
                    <h6 class="mb-2">Timeline</h6>
                    <div id="activityTimeline" class="border rounded p-3" style="max-height:360px; overflow:auto;">
                        <div class="text-muted">No activity yet.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light-brand" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<style>
    @media (max-width: 768px) {

        table#leadTable,
        div#leadTable_wrapper {
            display: none !important;
        }

        #mobileCards {
            display: block !important;
        }
        .offcanvas-end {
            width:calc(100% - 20%) !important; 
        }
    }
    

</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const ROUTE_DATATABLE = "{{ route('leads.datatable') }}";
const ROUTE_STORE     = "{{ route('leads.store') }}";
const ROUTE_SHOW      = "{{ url('leads') }}";
const ROUTE_UPDATE    = "{{ url('leads') }}";
const ROUTE_DELETE    = "{{ url('leads') }}";

const ROUTE_ORG_OPTIONS = "{{ route('leads.org_options') }}";
const ROUTE_ORG_CONTACTS = "{{ url('organizations') }}"; // /{id}/contacts
const ROUTE_CONTACT_DETAILS = "{{ url('organization-contacts') }}"; // /{id}

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('leadModal'));

    table = $('#leadTable').DataTable({
        processing:true, serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){ 
                d.status_stage_id = $('#f_status_stage_id').val(); 
                d.search_text = $('#f_search_text').val();
                d.date_from = $('#f_date_from').val();
                d.date_to = $('#f_date_to').val();
                @if(!empty($branches))
                d.branch_id = $('#f_branch_id').val();
                @endif
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'lead_no', name:'lead_no'},
            {data:'org_name', orderable:false, searchable:false}, 
            {data:'person_name', name:'person_name'},
            {data:'person_phone', name:'person_phone'}, 
            {data:'status_badge', orderable:false, searchable:false},
            {data:'next_followup', orderable:false, searchable:false},
            {data:'lead_state', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ],
        drawCallback: function (settings) {
            renderMobileCards(this.api().rows().data().toArray());
        }
    });

    function renderMobileCards(data) {
        let html = '';

        data.forEach(item => {
            html += `
            <div class="card mb-2">
                <div class="card-body p-2">
                    <h6>${item.person_name}</h6>
                    <small>${item.person_phone}</small><br>
                    <small>${item.org_name}</small><br>
                    <small>${item.status_badge}</small><br>
                    <small>${item.next_followup}</small>
                    <div class="mt-2">${item.action}</div>
                </div>
            </div>`;
        });

        $('#mobileCards').html(html);
    }


    $('#f_status_stage_id,#f_date_from,#f_date_to').on('change', ()=>table.ajax.reload());
    $('#f_search_text').on('keyup', ()=>table.ajax.reload());
    @if(!empty($branches))
    $('#f_branch_id').on('change', ()=>table.ajax.reload());
    @endif

    $('#btnReset').on('click', function(){
        $('#f_status_stage_id,#f_search_text,#f_date_from,#f_date_to').val('');
        @if(!empty($branches)) $('#f_branch_id').val(''); @endif
        table.ajax.reload();
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Lead');
        loadOrganizations();
        modal.show();
    });

    $('#organization_id').on('change', function(){
        const orgId = $(this).val();
        $('#organization_contact_id').html(`<option value="">Loading...</option>`);
        if(!orgId){
            $('#organization_contact_id').html(`<option value="">-- Select Contact --</option>`);
            return;
        }
        $.get(ROUTE_ORG_CONTACTS + '/' + orgId + '/contacts', function(rows){
            let html = `<option value="">-- Select Contact --</option>`;
            rows.forEach(r => html += `<option value="${r.id}">${r.name}</option>`);
            $('#organization_contact_id').html(html);
        });
    });

    $('#organization_contact_id').on('change', function(){
        const cid = $(this).val();
        if(!cid) return;
        $.get(ROUTE_CONTACT_DETAILS + '/' + cid, function(res){
            const d = res.data;
            if(d.name) $('#person_name').val(d.name);
            if(d.email) $('#person_email').val(d.email);
            if(d.phone) $('#person_phone').val(d.phone);
            // যদি phone_primary হয়:
            if(!d.phone && d.phone_primary) $('#person_phone').val(d.phone_primary);
        });
    });

    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#lead_id').val();

        let fd = new FormData();
        fd.append('organization_id', $('#organization_id').val());
        fd.append('organization_contact_id', $('#organization_contact_id').val());

        fd.append('person_name', $('#person_name').val());
        fd.append('person_phone', $('#person_phone').val());
        fd.append('person_email', $('#person_email').val());

        fd.append('platform_id', $('#platform_id').val());
        fd.append('status_stage_id', $('#status_stage_id').val());
        fd.append('assigned_user_id', $('#assigned_user_id').val());

        fd.append('subject', $('#subject').val());
        fd.append('note', $('#note').val());
        fd.append('expected_value', $('#expected_value').val());
        fd.append('existing_machine', $('#existing_machine').val());

        fd.append('next_followup_at', $('#next_followup_at').val());
        fd.append('next_action_type', $('#next_action_type').val());

        fd.append('lead_state', $('#lead_state').val());
        fd.append('lost_reason', $('#lost_reason').val());

        const url = id ? (ROUTE_UPDATE+'/'+id) : ROUTE_STORE;

        $.ajax({
            url:url, type:"POST", data:fd,
            processData:false, contentType:false,
            success:function(res){
                Swal.fire('Success', res.message ?? 'Saved', 'success');
                modal.hide();
                table.ajax.reload(null,false);
            },
            error:function(xhr){ showAjaxError(xhr); }
        });
    });

    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            const d = res.data;

            $('#modalTitle').text('Edit Lead');
            $('#lead_id').val(d.id);

            loadOrganizations(d.organization_id);

            setTimeout(()=>{ // wait org options load
                $('#organization_id').val(d.organization_id).trigger('change');
                setTimeout(()=>{ // wait contacts load
                    $('#organization_contact_id').val(d.organization_contact_id);
                }, 400);
            }, 400);

            $('#person_name').val(d.person_name);
            $('#person_phone').val(d.person_phone);
            $('#person_email').val(d.person_email);

            $('#platform_id').val(d.platform_id);
            $('#status_stage_id').val(d.status_stage_id);
            $('#assigned_user_id').val(d.assigned_user_id);

            $('#subject').val(d.subject);
            $('#note').val(d.note);
            $('#expected_value').val(d.expected_value);
            $('#existing_machine').val(d.existing_machine || '');

            $('#next_followup_at').val(d.next_followup_at ? d.next_followup_at.replace(' ','T') : '');
            $('#next_action_type').val(d.next_action_type);

            $('#lead_state').val(d.lead_state);
            $('#lost_reason').val(d.lost_reason);

            modal.show();
        });
    });

    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');
        Swal.fire({title:'Are you sure?', icon:'warning', showCancelButton:true}).then((r)=>{
            if(!r.isConfirmed) return;
            $.post(ROUTE_DELETE+'/'+id+'/delete', {}, function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            }).fail(xhr=>showAjaxError(xhr));
        });
    });
});

function loadOrganizations(selectedId = null){
    $.get(ROUTE_ORG_OPTIONS, function(rows){
        let html = `<option value="">-- Select Organization --</option>`;
        rows.forEach(r => html += `<option value="${r.id}">${r.name}</option>`);
        $('#organization_id').html(html);
        if(selectedId) $('#organization_id').val(selectedId);
    });
}

function clearForm(){
    $('#lead_id').val('');
    document.getElementById('leadForm').reset();
    $('#expected_value').val('0');
    $('#existing_machine').val('');
    $('#lead_state').val('open');
    $('#assigned_user_id').val('{{ auth()->id() }}');
    $('#organization_contact_id').html(`<option value="">-- Select Contact --</option>`);
}

function showAjaxError(xhr){
    let msg = 'Something went wrong';
    if(xhr.status === 422 && xhr.responseJSON){
        if(xhr.responseJSON.message) msg = xhr.responseJSON.message;
        else if(xhr.responseJSON.errors){
            const k = Object.keys(xhr.responseJSON.errors)[0];
            msg = xhr.responseJSON.errors[k][0];
        }
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}

// ✅ Quotation button (placeholder)
$(document).on('click', '.btn-quotation', function(){
    const leadId = $(this).data('id');
    Swal.fire('Quotation', 'Create Quotation from Lead ID: ' + leadId, 'info');
});

// ✅ Sales button (placeholder)
$(document).on('click', '.btn-sales', function(){
    const leadId = $(this).data('id');
    Swal.fire('Sales', 'Create Sales from Lead ID: ' + leadId, 'success');
});

const ROUTE_ACTIVITIES = "{{ url('leads') }}"; // /{id}/activities
let activityModal;

$(document).ready(function(){
    activityModal = new bootstrap.Modal(document.getElementById('activityModal'));

    // ✅ open activity modal
    $(document).on('click', '.btn-activity', function(){
        const leadId = $(this).data('id');
        $('#activity_lead_id').val(leadId);

        // reset form
        $('#a_activity_text').val('');
        $('#a_outcome_status').val('');
        $('#a_activity_at').val('');
        $('#a_next_followup_at').val('');
        $('#a_next_action_type').val('');

        loadActivities(leadId);
        activityModal.show();
    });

    // ✅ add activity
    $('#btnAddActivity').on('click', function(e){
        e.preventDefault();
        const leadId = $('#activity_lead_id').val();
        if(!leadId) return;

        let fd = new FormData();
        fd.append('activity_type', $('#a_activity_type').val());
        fd.append('activity_text', $('#a_activity_text').val());
        fd.append('outcome_status', $('#a_outcome_status').val());
        fd.append('activity_at', $('#a_activity_at').val());
        fd.append('next_action_type', $('#a_next_action_type').val());
        fd.append('next_followup_at', $('#a_next_followup_at').val());

        $.ajax({
            url: ROUTE_ACTIVITIES + '/' + leadId + '/activities',
            type:'POST',
            data: fd,
            processData:false,
            contentType:false,
            success:function(res){
                Swal.fire('Success', res.message ?? 'Added', 'success');
                loadActivities(leadId);
                table.ajax.reload(null,false); // ✅ refresh lead list next followup badge/time
            },
            error:function(xhr){ showAjaxError(xhr); }
        });
    });
});

function loadActivities(leadId){
    $('#activityTimeline').html('<div class="text-muted">Loading...</div>');

    $.get(ROUTE_ACTIVITIES + '/' + leadId + '/activities', function(res){
        const rows = res.data || [];
        if(rows.length === 0){
            $('#activityTimeline').html('<div class="text-muted">No activity yet.</div>');
            return;
        }

        let html = '';
        rows.forEach(r=>{
            const type = (r.activity_type || 'note').toUpperCase();
            const next = r.next_followup_at ? ` | Next: <b>${r.next_followup_at}</b> ${r.next_action_type ? `<span class="badge bg-secondary ms-1">${(r.next_action_type||'').toUpperCase()}</span>`:''}` : '';
            html += `
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div><span class="badge bg-info">${type}</span></div>
                        <small class="text-muted">${r.activity_at ?? ''}</small>
                    </div>
                    <div class="mt-2">${escapeHtml(r.activity_text ?? '')}</div>
                    <div class="mt-2">
                        ${r.outcome_status ? `<span class="badge bg-light text-dark" style="border:1px solid #eee;">${escapeHtml(r.outcome_status)}</span>`:''}
                        <small class="text-muted">${next}</small>
                    </div>
                </div>
            `;
        });

        $('#activityTimeline').html(html);
    }).fail(function(xhr){
        $('#activityTimeline').html('<div class="text-danger">Failed to load activities</div>');
    });
}

function escapeHtml(text) {
    return (text ?? '').replace(/[&<>"']/g, function(m) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
    });
}

</script>
@endpush