@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Platforms
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Platforms</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">System</li>
                <li class="breadcrumb-item">Platforms</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <button type="button" class="btn btn-primary" id="btnOpenCreate">
                <i class="feather-plus me-2"></i> Add Platform
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="platformTable" style="width:100%">
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
<div class="modal fade" id="platformModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Platform</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="platformForm">
                    @csrf
                    <input type="hidden" id="platform_id">

                    <div class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label">Title *</label>
                            <input type="text" id="title" class="form-control" placeholder="e.g. Facebook" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status *</label>
                            <select id="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
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
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

const ROUTE_DATATABLE = "{{ route('platforms.datatable') }}";
const ROUTE_STORE     = "{{ route('platforms.store') }}";
const ROUTE_SHOW      = "{{ url('business/platforms') }}";
const ROUTE_UPDATE    = "{{ url('business/platforms') }}";
const ROUTE_DELETE    = "{{ url('business/platforms') }}";

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('platformModal'));

    table = $('#platformTable').DataTable({
        processing:true,
        serverSide:true,
        ajax: ROUTE_DATATABLE,
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'title', name:'title'},
            {data:'status', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Platform');
        modal.show();
    });

    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#platform_id').val();

        let fd = new FormData();
        fd.append('title', $('#title').val());
        fd.append('status', $('#status').val());

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

    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            $('#modalTitle').text('Edit Platform');
            $('#platform_id').val(res.data.id);
            $('#title').val(res.data.title);
            $('#status').val(res.data.status ? 1 : 0);
            modal.show();
        });
    });

    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');
        Swal.fire({
            title:'Are you sure?',
            text:'This platform will be deleted!',
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

function clearForm(){
    $('#platform_id').val('');
    document.getElementById('platformForm').reset();
    $('#status').val('1');
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