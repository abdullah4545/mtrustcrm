@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Geo Districts
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
                <li class="breadcrumb-item">Districts</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add District
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                {{-- ✅ Division Filter --}}
                <div class="row mb-3">
                    <div class="col-xl-4">
                        <label class="form-label">Filter by Division</label>
                        <select class="form-control" id="filterDivision">
                            <option value="">-- All Divisions --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="districtsTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Division</th>
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


@push('modals')
<div class="modal fade" id="districtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add District</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="districtForm">
                    @csrf
                    <input type="hidden" id="district_id" value="">

                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Division *</label>
                                <select class="form-control" id="division_id" name="division_id" required>
                                    <option value="">-- Select Division --</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="form-group mb-3">
                                <label class="form-label">District Name *</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" name="code" id="code" placeholder="DHK / CTG ...">
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .modal-backdrop{ z-index: 1040 !important; }
    .modal{ z-index: 1055 !important; }
</style>

<script>
const ROUTE_DATATABLE = "{{ route('districts.datatable') }}";
const ROUTE_STORE     = "{{ route('districts.store') }}";
const ROUTE_SHOW      = "{{ url('geo/districts') }}";
const ROUTE_UPDATE    = "{{ url('geo/districts') }}";
const ROUTE_DELETE    = "{{ url('geo/districts') }}";

let table;
let districtModal;

$(document).ready(function () {

    districtModal = new bootstrap.Modal(document.getElementById('districtModal'), {
        backdrop: true,
        keyboard: true
    });

    table = $('#districtsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ROUTE_DATATABLE,
            data: function (d) {
                d.division_id = $('#filterDivision').val(); // ✅ pass filter
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false},
            {data: 'division', name: 'division.name', orderable:false},
            {data: 'name', name: 'name'},
            {data: 'code', name: 'code'},
            {data: 'is_active', name: 'is_active', orderable:false, searchable:false}, 
            {data: 'action', name: 'action', orderable:false, searchable:false},
        ]
    });

    // ✅ Filter reload
    $('#filterDivision').on('change', function(){
        table.ajax.reload();
    });

});

// open create
$(document).on('click', '#btnOpenCreate', function(){
    clearForm();
    $('#modalTitle').text('Add District');

    // optional: filter selected division auto set
    const selectedDiv = $('#filterDivision').val();
    if(selectedDiv){
        $('#division_id').val(selectedDiv);
    }

    districtModal.show();
});

// save
$(document).on('click', '#btnSave', function(e){
    e.preventDefault();

    let id = $('#district_id').val();
    let is_active = $('#is_active').is(':checked') ? 1 : 0;

    let payload = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        division_id: $('#division_id').val(),
        name: $('#name').val(),
        code: $('#code').val(),
        is_active: is_active
    };

    if(!id){
        $.ajax({
            url: ROUTE_STORE,
            type: "POST",
            data: payload,
            success: function(res){
                Swal.fire('Success', res.message ?? 'Created', 'success');
                districtModal.hide();
                table.ajax.reload(null, false);
            },
            error: function(xhr){ showAjaxError(xhr); }
        });
        return;
    }

    payload._method = "PUT";
    $.ajax({
        url: ROUTE_UPDATE + '/' + id,
        type: "POST",
        data: payload,
        success: function(res){
            Swal.fire('Success', res.message ?? 'Updated', 'success');
            districtModal.hide();
            table.ajax.reload(null, false);
        },
        error: function(xhr){ showAjaxError(xhr); }
    });
});

// edit
$(document).on('click', '.btn-edit', function(){
    let id = $(this).data('id');

    $.get(ROUTE_SHOW + '/' + id, function(res){
        clearForm();
        $('#modalTitle').text('Edit District');
        $('#district_id').val(res.data.id);
        $('#division_id').val(res.data.division_id);
        $('#name').val(res.data.name);
        $('#code').val(res.data.code);
        $('#is_active').prop('checked', !!res.data.is_active);

        districtModal.show();
    });
});

// delete
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
            error: function(xhr){ showAjaxError(xhr); }
        });
    });
});

function clearForm(){
    $('#district_id').val('');
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
