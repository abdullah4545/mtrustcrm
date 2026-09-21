@extends('backend.master')

@section('title')
{{ ($business?->business_name ?? 'Medi Trust Solution') }} - Quick Lead Create
@endsection

@section('maincontent')

<div class="nxl-content">

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <ul class="breadcrumb mt-1">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                <li class="breadcrumb-item active">Quick Create</li>
            </ul>
        </div>
    </div>

</div>

<div class="main-content">

    <div class="card">
        <div class="card-body">

            <form id="quickLeadForm">
                @csrf

                <div class="row g-2">

                    {{-- Organization --}}
                    <div class="col-md-6">
                        <label class="form-label">Organization</label>
                        <select name="organization_id" id="organization_id" class="form-control">
                            <option value="">-- Select Organization --</option>
                        </select>
                    </div>

                    {{-- Contact --}}
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <select name="organization_contact_id" id="organization_contact_id" class="form-control">
                            <option value="">-- Select Contact --</option>
                        </select>
                    </div>

                    {{-- Name --}}
                    <div class="col-md-4">
                        <label class="form-label">Contact Person Name</label>
                        <input type="text" name="person_name" id="person_name" class="form-control">
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-4">
                        <label class="form-label">Contact Person Phone</label>
                        <input type="text" name="person_phone" id="person_phone" class="form-control">
                    </div>

                    {{-- Email --}}
                    <div class="col-md-4">
                        <label class="form-label">Contact Person Email</label>
                        <input type="email" name="person_email" id="person_email" class="form-control">
                    </div>

                    {{-- Platform --}}
                    <div class="col-md-4">
                        <label class="form-label">Source</label>
                        <select name="platform_id" id="platform_id" class="form-control">
                            <option value="">--</option>
                            @foreach($platforms as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status_stage_id" id="status_stage_id" class="form-control">
                            <option value="">--</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- State --}}
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <select name="lead_state" id="lead_state" class="form-control">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    {{-- Medical Reagents --}}
                    <div class="col-md-12">
                        <label class="form-label">Medical Reagents</label>
                        <input type="text" name="subject" id="subject" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Existing Machine</label>
                        <textarea name="existing_machine" id="existing_machine" class="form-control" rows="5" placeholder="Existing machine / model"></textarea>
                    </div>

                    {{-- Note --}}
                    <div class="col-md-12">
                        <label class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control"></textarea>
                    </div>

                    {{-- Value --}}
                    <div class="col-md-3">
                        <label class="form-label">Expected Value</label>
                        <input type="number" name="expected_value" id="expected_value" class="form-control" value="0">
                    </div>

                    {{-- Followup --}}
                    <div class="col-md-3">
                        <label class="form-label">Next Followup</label>
                        <input type="datetime-local" name="next_followup_at" id="next_followup_at" class="form-control">
                    </div>

                    {{-- Action --}}
                    <div class="col-md-3">
                        <label class="form-label">Next Action</label>
                        <select name="next_action_type" id="next_action_type" class="form-control">
                            <option value="">--</option>
                            <option value="call">Call</option>
                            <option value="visit">Visit</option>
                            <option value="message">Message</option>
                            <option value="meeting">Meeting</option>
                        </select>
                    </div>

                    {{-- Lost --}}
                    <div class="col-md-3">
                        <label class="form-label">Lost Reason</label>
                        <input type="text" name="lost_reason" id="lost_reason" class="form-control">
                    </div>

                </div>

                <div class="mt-3 text-end">
                    <button type="submit" id="btnSubmit" class="btn btn-primary">
                        Save Lead
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
window.quickLeadExistingMachineEditor=null;
ClassicEditor.create(document.querySelector('#existing_machine'), {toolbar:['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo']}).then(e=>window.quickLeadExistingMachineEditor=e).catch(console.error);
</script>
<script>
    const ROUTE_ORG_OPTIONS = "{{ route('leads.org_options') }}";
    const ROUTE_ORG_CONTACTS = "{{ url('organizations') }}";
    const ROUTE_CONTACT_DETAILS = "{{ url('organization-contacts') }}";

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $(function () {
        loadOrganizations();
        // ================= SAVE LEAD =================
        $('#quickLeadForm').on('submit', function (e) {
            e.preventDefault();

            let btn = $('#btnSubmit');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('leads.quickStore') }}",
                type: "POST",
                data: (function(form){ if(window.quickLeadExistingMachineEditor) $('#existing_machine').val(window.quickLeadExistingMachineEditor.getData()); return new FormData(form); })(this),
                processData: false,
                contentType: false,

                success: function (res) {
                    Swal.fire('Success', res.message, 'success');
                    $('#quickLeadForm')[0].reset(); if(window.quickLeadExistingMachineEditor) window.quickLeadExistingMachineEditor.setData('');
                },

                error: function (xhr) {
                    let msg = "Something went wrong";

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        msg = Object.values(errors)[0][0];
                    }

                    Swal.fire('Error', msg, 'error');
                },

                complete: function () {
                    btn.prop('disabled', false).text('Save Lead');
                }
            });
        });

        // ================= LOAD CONTACTS =================
        $('#organization_id').on('change', function () {

            let orgId = $(this).val();

            $('#organization_contact_id').html(`<option value="">Loading...</option>`);

            if (!orgId) {
                $('#organization_contact_id').html(`<option value="">-- Select Contact --</option>`);
                return;
            }

            $.get(`${ROUTE_ORG_CONTACTS}/${orgId}/contacts`, function (rows) {

                let html = `<option value="">-- Select Contact --</option>`;

                rows.forEach(r => {
                    html += `<option value="${r.id}">${r.name}</option>`;
                });

                $('#organization_contact_id').html(html);
            });
        });

        // ================= AUTO FILL CONTACT =================
        $('#organization_contact_id').on('change', function () {

            let cid = $(this).val();
            if (!cid) return;

            $.get(`${ROUTE_CONTACT_DETAILS}/${cid}`, function (res) {

                let d = res.data;

                $('#person_name').val(d.name ?? '');
                $('#person_email').val(d.email ?? '');
                $('#person_phone').val(d.phone ?? d.phone_primary ?? '');

            });
        });

        function loadOrganizations(selectedId = null){
    const $org = $('#organization_id');
    if ($org.hasClass('select2-hidden-accessible')) $org.select2('destroy');
    $org.empty().append(new Option('-- Select Organization --', '', false, false));
    $org.select2({
        width:'100%',
        placeholder:'Search organization...',
        allowClear:false,
        minimumInputLength:0,
        ajax:{
            url:ROUTE_ORG_OPTIONS,
            dataType:'json',
            delay:300,
            data:params=>({q:params.term||''}),
            processResults:rows=>({results:(rows||[]).map(r=>({id:r.id,text:r.name+(r.phone_primary?' · '+r.phone_primary:'')}))}),
            cache:true
        }
    });
    if(selectedId){
        $.get(ROUTE_ORG_OPTIONS,{id:selectedId},function(rows){
            if(rows && rows.length){
                const r=rows[0];
                const option=new Option(r.name+(r.phone_primary?' · '+r.phone_primary:''),r.id,true,true);
                $org.append(option).trigger('change');
            }
        });
    }
}

    });
</script>
@endpush