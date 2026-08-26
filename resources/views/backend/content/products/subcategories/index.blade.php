@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Product Subcategories
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Products</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Subcategories</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Subcategory
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                {{-- ✅ Filter row (auto works) --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <select id="f_category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-light-brand" id="btnReset">Reset</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="subTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Name</th>
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
    {{-- ✅ Modal --}}
    <div class="modal fade" id="subModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="subForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="sub_id">

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select id="category_id" class="form-control" required>
                                    <option value="">Select</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select id="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Name *</label>
                                <input type="text" id="name" class="form-control" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Image</label>
                                <input type="file" id="image" class="form-control" accept="image/*">
                                <div id="imgPreview" class="mt-2"></div>
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
</style>

<script>
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

const ROUTE_DATATABLE = "{{ route('product.subcategories.datatable') }}";
const ROUTE_STORE     = "{{ route('product.subcategories.store') }}";
const ROUTE_SHOW      = "{{ url('products/subcategories') }}";
const ROUTE_UPDATE    = "{{ url('products/subcategories') }}";
const ROUTE_DELETE    = "{{ url('products/subcategories') }}";

let table, modal;

$(document).ready(function(){
    modal = new bootstrap.Modal(document.getElementById('subModal'));

    table = $('#subTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){
                d.category_id = $('#f_category_id').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'image', orderable:false, searchable:false},
            {data:'category', orderable:false, searchable:false},
            {data:'name', name:'name'},
            {data:'status', orderable:false, searchable:false}, 
            {data:'action', orderable:false, searchable:false},
        ]
    });

    // ✅ auto filter
    $('#f_category_id').on('change', function(){
        table.ajax.reload();
    });

    $('#btnReset').on('click', function(){
        $('#f_category_id').val('');
        table.ajax.reload();
    });

    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Subcategory');
        modal.show();
    });

    $('#btnSave').on('click', function(e){
        e.preventDefault();
        const id = $('#sub_id').val();

        let fd = new FormData();
        fd.append('category_id', $('#category_id').val());
        fd.append('name', $('#name').val());
        fd.append('status', $('#status').val());
        if($('#image')[0].files[0]) fd.append('image', $('#image')[0].files[0]);

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

        fd.append('_method','PUT');

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
            const d = res.data;

            $('#modalTitle').text('Edit Subcategory');
            $('#sub_id').val(d.id);

            $('#category_id').val(d.category_id);
            $('#name').val(d.name);
            $('#status').val(d.status);

            if(d.image){
                $('#imgPreview').html(`<img src="{{ asset('') }}${d.image}" style="height:60px;border-radius:8px;">`);
            }

            modal.show();
        });
    });

    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This subcategory will be deleted!',
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
    $('#sub_id').val('');
    document.getElementById('subForm').reset();
    $('#imgPreview').html('');
    $('#status').val('active');
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
