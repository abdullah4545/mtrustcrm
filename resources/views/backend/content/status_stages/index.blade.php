@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - StatusStage
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">StatusStage</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">System</li>
                <li class="breadcrumb-item">StatusStage</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <button type="button" class="btn btn-primary" id="btnOpenCreate" style="line-height: 28px !important;">
                <i class="feather-plus me-2"></i> Add New
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select id="filter_is_for" class="form-control">
                            <option value="">All Module</option>
                            <option value="lead">Lead</option>
                            <option value="sales">Sales</option>
                            <option value="quotation">Quotation</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-sm" id="btnResetFilter">Reset</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="ssTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Is For</th>
                            <th>Color</th>
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
<div class="modal fade" id="ssModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="ssForm">
                    @csrf
                    <input type="hidden" id="ss_id">

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Is For *</label>
                            <select id="is_for" class="form-control">
                                <option value="lead">Lead</option>
                                <option value="sales">Sales</option>
                                <option value="quotation">Quotation</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select id="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Name *</label>
                            <input type="text" id="name" class="form-control" placeholder="e.g. Qualified">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Color *</label>
                            <div class="d-flex gap-2">
                                <input type="color" id="color_picker" class="form-control form-control-color" value="#3b82f6" style="width:60px;">
                                <input type="text" id="color" class="form-control" value="#3b82f6">
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

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const ROUTE_DATATABLE = "{{ route('status_stages.datatable') }}";
const ROUTE_STORE     = "{{ route('status_stages.store') }}";
const ROUTE_SHOW      = "{{ url('business/status-stages') }}";
const ROUTE_UPDATE    = "{{ url('business/status-stages') }}";
const ROUTE_DELETE    = "{{ url('business/status-stages') }}";

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('ssModal'));

    table = $('#ssTable').DataTable({
        processing:true,
        serverSide:true,
        ajax: {
            url: ROUTE_DATATABLE,
            data: function(d){
                d.is_for = $('#filter_is_for').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'name_dot', orderable:false, searchable:true},
            {data:'is_for', orderable:false, searchable:false},
            {data:'color', name:'color'},
            {data:'status', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#filter_is_for').on('change', ()=> table.ajax.reload());
    $('#btnResetFilter').on('click', function(){
        $('#filter_is_for').val('');
        table.ajax.reload();
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add');
        modal.show();
    });

    // color sync
    $('#color_picker').on('input', function(){ $('#color').val($(this).val()); });
    $('#color').on('keyup', function(){
        const v = $(this).val();
        if(/^#([0-9A-F]{3}){1,2}$/i.test(v)) $('#color_picker').val(v);
    });

    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#ss_id').val();

        let fd = new FormData();
        fd.append('is_for', $('#is_for').val());
        fd.append('name', $('#name').val());
        fd.append('color', $('#color').val());
        fd.append('status', $('#status').val());

        const url = id ? (ROUTE_UPDATE+'/'+id) : ROUTE_STORE;

        $.ajax({
            url: url,
            type: "POST",
            data: fd,
            processData:false,
            contentType:false,
            success: function(res){
                Swal.fire('Success', res.message ?? 'Saved', 'success');
                modal.hide();
                table.ajax.reload(null,false);
            },
            error: function(xhr){ showAjaxError(xhr); }
        });
    });

    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            const d = res.data;
            $('#modalTitle').text('Edit');
            $('#ss_id').val(d.id);
            $('#is_for').val(d.is_for);
            $('#name').val(d.name);
            $('#color').val(d.color);
            $('#color_picker').val(d.color);
            $('#status').val(d.status ? 1 : 0);
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

function clearForm(){
    $('#ss_id').val('');
    document.getElementById('ssForm').reset();
    $('#color').val('#3b82f6');
    $('#color_picker').val('#3b82f6');
    $('#status').val('1');
}

function showAjaxError(xhr){
    let msg = 'Something went wrong';
    if(xhr.status === 422 && xhr.responseJSON){
        msg = xhr.responseJSON.message ?? (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors)[0][0] : msg);
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}
</script>
@endpush