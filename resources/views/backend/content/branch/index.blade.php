@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Branches
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Branches</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Branches</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Branch
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="branchTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Geo</th>
                            <th>Main</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('modals')
<div class="modal fade" id="branchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="branchForm">
                    @csrf
                    <input type="hidden" id="branch_id">

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Branch Code *</label>
                            <input type="text" id="branch_code" class="form-control" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Branch Name *</label>
                            <input type="text" id="branch_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Parent Branch</label>
                            <select id="parent_branch_id" class="form-control">
                                <option value="">-- None --</option>
                                @foreach($parents as $p)
                                    <option value="{{ $p->id }}">{{ $p->branch_name }} ({{ $p->branch_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Main Branch *</label>
                            <select id="is_main_branch" class="form-control" required>
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status *</label>
                            <select id="is_active" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="address" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" id="phone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control">
                        </div>

                        {{-- GEO --}}
                        <div class="col-md-3">
                            <label class="form-label">Division *</label>
                            <select id="division_id" class="form-control" required>
                                <option value="">Select Division</option>
                                @foreach($divisions as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">District</label>
                            <select id="district_id" class="form-control">
                                <option value="">All Districts</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Upazila</label>
                            <select id="upazila_id" class="form-control">
                                <option value="">All Upazilas</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Union</label>
                            <select id="union_id" class="form-control">
                                <option value="">All Unions</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSave">Save</button>
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
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const ROUTE_DATATABLE = "{{ route('branches.datatable') }}";
const ROUTE_STORE     = "{{ route('branches.store') }}";
const ROUTE_SHOW      = "{{ url('system/branches') }}";
const ROUTE_UPDATE    = "{{ url('system/branches') }}";
const ROUTE_DELETE    = "{{ url('system/branches') }}";

const ROUTE_DISTRICTS = "{{ url('/geo/districts') }}";
const ROUTE_UPAZILAS  = "{{ url('/geo/upazilas') }}";
const ROUTE_UNIONS    = "{{ url('/geo/unions') }}";

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('branchModal'));

    table = $('#branchTable').DataTable({
        processing:true,
        serverSide:true,
        ajax: ROUTE_DATATABLE,
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'branch_code', name:'branch_code'},
            {data:'branch_name', name:'branch_name'},
            {data:'parent_branch', orderable:false, searchable:false},
            {data:'geo', orderable:false, searchable:false},
            {data:'is_main_branch', orderable:false, searchable:false},
            {data:'is_active', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Branch');
        modal.show();
    });

    // cascading
    $('#division_id').on('change', function(){
        const divId = $(this).val();
        resetGeo('district');
        if(!divId) return;

        $.get(ROUTE_DISTRICTS+'/'+divId, function(res){
            res.forEach(r => $('#district_id').append(`<option value="${r.id}">${r.name}</option>`));
        });
    });

    $('#district_id').on('change', function(){
        const distId = $(this).val();
        resetGeo('upazila');
        if(!distId) return;

        $.get(ROUTE_UPAZILAS+'/'+distId, function(res){
            res.forEach(r => $('#upazila_id').append(`<option value="${r.id}">${r.name}</option>`));
        });
    });

    $('#upazila_id').on('change', function(){
        const upaId = $(this).val();
        resetGeo('union');
        if(!upaId) return;

        $.get(ROUTE_UNIONS+'/'+upaId, function(res){
            res.forEach(r => $('#union_id').append(`<option value="${r.id}">${r.name}</option>`));
        });
    });

    // save
    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#branch_id').val();

        let fd = new FormData();
        fd.append('branch_code', $('#branch_code').val());
        fd.append('branch_name', $('#branch_name').val());
        fd.append('parent_branch_id', $('#parent_branch_id').val());
        fd.append('is_main_branch', $('#is_main_branch').val());
        fd.append('is_active', $('#is_active').val());
        fd.append('address', $('#address').val());
        fd.append('phone', $('#phone').val());
        fd.append('email', $('#email').val());

        fd.append('division_id', $('#division_id').val());
        fd.append('district_id', $('#district_id').val());
        fd.append('upazila_id', $('#upazila_id').val());
        fd.append('union_id', $('#union_id').val());

        if(!id){
            $.ajax({
                url: ROUTE_STORE,
                type: "POST",
                data: fd,
                processData:false,
                contentType:false,
                success: function(res){
                    Swal.fire('Success', res.message ?? 'Created', 'success');
                    modal.hide(); table.ajax.reload(null,false);
                },
                error: function(xhr){ showAjaxError(xhr); }
            });
            return;
        }

        $.ajax({
            url: ROUTE_UPDATE + '/' + id,
            type: "POST",
            data: fd,
            processData:false,
            contentType:false,
            success: function(res){
                Swal.fire('Success', res.message ?? 'Updated', 'success');
                modal.hide(); table.ajax.reload(null,false);
            },
            error: function(xhr){ showAjaxError(xhr); }
        });
    });

    // edit
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            const d = res.data;

            $('#modalTitle').text('Edit Branch');
            $('#branch_id').val(d.id);

            $('#branch_code').val(d.branch_code);
            $('#branch_name').val(d.branch_name);
            $('#parent_branch_id').val(d.parent_branch_id ?? '');
            $('#is_main_branch').val(d.is_main_branch ? 1 : 0);
            $('#is_active').val(d.is_active ? 1 : 0);
            $('#address').val(d.address ?? '');
            $('#phone').val(d.phone ?? '');
            $('#email').val(d.email ?? '');

            // set geo with cascading load
            $('#division_id').val(d.division_id).trigger('change');

            // wait then set district/upazila/union
            setTimeout(() => {
                if(d.district_id) {
                    $('#district_id').val(d.district_id).trigger('change');
                    setTimeout(() => {
                        if(d.upazila_id) {
                            $('#upazila_id').val(d.upazila_id).trigger('change');
                            setTimeout(() => {
                                if(d.union_id) $('#union_id').val(d.union_id);
                            }, 400);
                        }
                    }, 400);
                }
            }, 400);

            modal.show();
        });
    });

    // delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This branch will be deleted!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Yes, delete it!'
        }).then((r)=>{
            if(!r.isConfirmed) return;

            $.post(ROUTE_DELETE+'/'+id+'/delete', {}, function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            }).fail(xhr => showAjaxError(xhr));
        });
    });
});

function resetGeo(level){
    if(level === 'district'){
        $('#district_id').html('<option value="">All Districts</option>');
        $('#upazila_id').html('<option value="">All Upazilas</option>');
        $('#union_id').html('<option value="">All Unions</option>');
    }
    if(level === 'upazila'){
        $('#upazila_id').html('<option value="">All Upazilas</option>');
        $('#union_id').html('<option value="">All Unions</option>');
    }
    if(level === 'union'){
        $('#union_id').html('<option value="">All Unions</option>');
    }
}

function clearForm(){
    $('#branch_id').val('');
    document.getElementById('branchForm').reset();
    $('#is_active').val('1'); $('#is_main_branch').val('0');
    resetGeo('district');
}

function showAjaxError(xhr){
    let msg = 'Something went wrong';
    if(xhr.status === 422 && xhr.responseJSON){
        if(xhr.responseJSON.errors){
            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
            msg = xhr.responseJSON.errors[firstKey][0];
        } else if(xhr.responseJSON.message){
            msg = xhr.responseJSON.message;
        }
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}
</script>
@endpush