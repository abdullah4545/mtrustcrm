@extends('backend.master')

@section('title')
{{ ($business?->business_name ?? 'Medi Trust Solution') }} - Vehicles
@endsection

@section('maincontent')

<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Business</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Vehicles</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <button class="btn btn-primary" id="btnOpenCreate">
                <i class="feather-plus me-2"></i> Add New
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered" id="vehicleTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
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
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="vehicleForm">
                    @csrf
                    <input type="hidden" id="vehicle_id">

                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" id="title" class="form-control" placeholder="Vehicle name">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select id="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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

const ROUTE_DATATABLE = "{{ route('vehicles.datatable') }}";
const ROUTE_STORE     = "{{ route('vehicles.store') }}";
const ROUTE_SHOW      = "{{ url('vehicles') }}";
const ROUTE_UPDATE    = "{{ url('vehicles') }}";
const ROUTE_DELETE    = "{{ url('vehicles') }}";

let table, modal;

$(document).ready(function(){

    modal = new bootstrap.Modal(document.getElementById('vehicleModal'));

    table = $('#vehicleTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'title'},
            {data:'status_badge', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Vehicle');
        modal.show();
    });

    $('#btnSave').on('click', function(e){
        e.preventDefault();

        let id = $('#vehicle_id').val();

        let fd = new FormData();
        fd.append('title', $('#title').val());
        fd.append('status', $('#status').val());

        let url = id ? (ROUTE_UPDATE + '/' + id) : ROUTE_STORE;

        $.ajax({
            url: url,
            type: "POST",
            data: fd,
            processData:false,
            contentType:false,
            success:function(res){
                Swal.fire('Success', res.message ?? 'Saved', 'success');
                modal.hide();
                table.ajax.reload(null,false);
            },
            error:function(xhr){
                showAjaxError(xhr);
            }
        });

    });

    $(document).on('click', '.btn-edit', function(){

        const id = $(this).data('id');

        $.get(ROUTE_SHOW + '/' + id + '/edit', function(res){

            clearForm();

            $('#modalTitle').text('Edit Vehicle');
            $('#vehicle_id').val(res.data.id);
            $('#title').val(res.data.title);
            $('#status').val(res.data.status);

            modal.show();
        });

    });

    $(document).on('click', '.btn-delete', function(){

        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            icon:'warning',
            showCancelButton:true
        }).then((r)=>{

            if(!r.isConfirmed) return;

            $.post(ROUTE_DELETE + '/' + id + '/delete', {}, function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            });

        });

    });

});

function clearForm(){
    $('#vehicle_id').val('');
    $('#vehicleForm')[0].reset();
}

function showAjaxError(xhr){
    let msg = 'Something went wrong';

    if(xhr.status === 422 && xhr.responseJSON){
        msg = xhr.responseJSON.message ?? msg;
    } else if(xhr.responseJSON?.message){
        msg = xhr.responseJSON.message;
    }

    Swal.fire('Error', msg, 'error');
}
</script>

@endpush