@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Roles
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">RBAC</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Roles</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Role
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="roleTable" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th style="width:220px">Name</th>
                            <th>Permissions</th>
                            <th style="width:160px">Action</th>
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
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="roleForm">
                    @csrf
                    <input type="hidden" id="role_id">

                    <div class="row g-2">
                        <div class="col-md-12">
                            <label class="form-label">Role Name *</label>
                            <input type="text" id="name" class="form-control" placeholder="e.g. manager" required>
                            <small class="text-muted">
                                lowercase recommended (manager, staff, accounts_branch)
                            </small>
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

<style>
.modal-backdrop{ z-index:1040 !important; }
.modal{ z-index:1055 !important; }
.table td { vertical-align: middle; }
.action-btns .btn { margin-right: 6px; }
</style>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// ✅ system prefix routes
const ROUTE_DATATABLE = "{{ route('roles.datatable') }}";
const ROUTE_STORE     = "{{ route('roles.store') }}";
const ROUTE_SHOW      = "{{ url('system/roles') }}";
const ROUTE_UPDATE    = "{{ url('system/roles') }}";
const ROUTE_DELETE    = "{{ url('system/roles') }}";

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('roleModal'));

    table = $('#roleTable').DataTable({
        processing:true,
        serverSide:true,
        ajax: ROUTE_DATATABLE,
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'name', name:'name'},
            {data:'permissions', orderable:false, searchable:false}, // ✅ badges html
            {data:'action', orderable:false, searchable:false},
        ]
    });

    // open create
    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Role');
        modal.show();
    });

    // save (create/update)
    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#role_id').val();

        let fd = new FormData();
        fd.append('name', $('#name').val());

        if(!id){
            $.ajax({
                url: ROUTE_STORE,
                type: "POST",
                data: fd,
                processData:false,
                contentType:false,
                success: function(res){
                    Swal.fire('Success', res.message ?? 'Created', 'success');
                    modal.hide();
                    table.ajax.reload(null,false);
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
                modal.hide();
                table.ajax.reload(null,false);
            },
            error: function(xhr){ showAjaxError(xhr); }
        });
    });

    // edit
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            $('#modalTitle').text('Edit Role');
            $('#role_id').val(res.data.id);
            $('#name').val(res.data.name);
            modal.show();
        });
    });

    // delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This role will be deleted!',
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

    // ✅ enable bootstrap tooltip for assign buttons (if bootstrap is available)
    document.addEventListener('mouseover', function(e){
        const t = e.target.closest('[data-bs-toggle="tooltip"]');
        if(!t) return;
        if(!t._tooltip){
            t._tooltip = new bootstrap.Tooltip(t);
        }
    });
});

function clearForm(){
    $('#role_id').val('');
    document.getElementById('roleForm').reset();
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
    } else if(xhr.status === 419){
        msg = 'CSRF token mismatch (419)';
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}
</script>
@endpush