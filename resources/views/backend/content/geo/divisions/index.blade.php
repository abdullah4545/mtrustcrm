@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Geo Divisions
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Geo</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Divisions</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Division
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered" id="divisionsTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th> 
                            <th width="140">Action</th>
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


{{-- ✅ Modal MUST be in body-level stack to avoid blur/click issue --}}
@push('modals')
<div class="modal fade" id="divisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="divisionForm">
                    @csrf
                    <input type="hidden" id="division_id" value="">

                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" name="code" id="code" placeholder="DHA / CTG ...">
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
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
<!-- ✅ DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- ✅ SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ✅ Fix z-index conflict (theme blur/backdrop issue) */
    .modal-backdrop{ z-index: 1040 !important; }
    .modal{ z-index: 1055 !important; }
</style>

<script>
const ROUTE_DATATABLE = "{{ route('divisions.datatable') }}";
const ROUTE_STORE     = "{{ route('divisions.store') }}";
const ROUTE_SHOW      = "{{ url('geo/divisions') }}";
const ROUTE_UPDATE    = "{{ url('geo/divisions') }}";
const ROUTE_DELETE    = "{{ url('geo/divisions') }}";

let table;
let divisionModal;

$(document).ready(function () {

    // ✅ Bootstrap modal instance (Bootstrap 5 safe)
    divisionModal = new bootstrap.Modal(document.getElementById('divisionModal'), {
        backdrop: true,
        keyboard: true
    });

    // ✅ Datatable init
    table = $('#divisionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: ROUTE_DATATABLE,
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false},
            {data: 'name', name: 'name'},
            {data: 'code', name: 'code'},
            {data: 'is_active', name: 'is_active', orderable:false, searchable:false}, 
            {data: 'action', name: 'action', orderable:false, searchable:false},
        ]
    });

});

// ✅ Open create modal
$(document).on('click', '#btnOpenCreate', function(){
    clearForm();
    $('#modalTitle').text('Add Division');
    divisionModal.show();
});

// ✅ Save (Create/Update)
$(document).on('click', '#btnSave', function(e){
    e.preventDefault();

    let id = $('#division_id').val();
    let is_active = $('#is_active').is(':checked') ? 1 : 0;

    let payload = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $('#name').val(),
        code: $('#code').val(),
        is_active: is_active
    };

    // ✅ Create
    if(!id){
        $.ajax({
            url: ROUTE_STORE,
            type: "POST",
            data: payload,
            success: function(res){
                Swal.fire('Success', res.message ?? 'Created', 'success');
                divisionModal.hide();
                table.ajax.reload(null, false);
            },
            error: function(xhr){
                showAjaxError(xhr);
            }
        });
        return;
    }

    // ✅ Update
    payload._method = "PUT";
    $.ajax({
        url: ROUTE_UPDATE + '/' + id,
        type: "POST",
        data: payload,
        success: function(res){
            Swal.fire('Success', res.message ?? 'Updated', 'success');
            divisionModal.hide();
            table.ajax.reload(null, false);
        },
        error: function(xhr){
            showAjaxError(xhr);
        }
    });
});

// ✅ Edit
$(document).on('click', '.btn-edit', function(){
    let id = $(this).data('id');

    $.get(ROUTE_SHOW + '/' + id, function(res){
        clearForm();
        $('#modalTitle').text('Edit Division');
        $('#division_id').val(res.data.id);
        $('#name').val(res.data.name);
        $('#code').val(res.data.code);
        $('#is_active').prop('checked', !!res.data.is_active);

        divisionModal.show();
    });
});

// ✅ Delete
$(document).on('click', '.btn-delete', function(){
    let id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "This will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(!result.isConfirmed) return;

        $.ajax({
            url: ROUTE_DELETE + '/' + id,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            },
            success: function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null, false);
            },
            error: function(xhr){
                showAjaxError(xhr);
            }
        });
    });
});

function clearForm(){
    $('#division_id').val('');
    $('#name').val('');
    $('#code').val('');
    $('#is_active').prop('checked', true);
}

function showAjaxError(xhr){
    let msg = 'Something went wrong';
    if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
        const firstKey = Object.keys(xhr.responseJSON.errors)[0];
        msg = xhr.responseJSON.errors[firstKey][0];
    } else if(xhr.status === 419){
        msg = 'CSRF token mismatch (419)';
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}
</script>
@endpush
