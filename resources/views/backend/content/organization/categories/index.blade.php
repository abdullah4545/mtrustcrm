@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Organization Categories
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Organization</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Categories</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Category
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered" id="orgCategoryTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
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
    {{-- Modal --}}
    <div class="modal fade" id="orgCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="orgCategoryForm">
                        @csrf
                        <input type="hidden" id="category_id">

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="description" rows="3"></textarea>
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
    .modal-backdrop{ z-index:1040 !important; }
    .modal{ z-index:1055 !important; }
    </style>

    <script>
    const ROUTE_DATATABLE = "{{ route('org.categories.datatable') }}";
    const ROUTE_STORE     = "{{ route('org.categories.store') }}";
    const ROUTE_SHOW      = "{{ url('organization/categories') }}";
    const ROUTE_UPDATE    = "{{ url('organization/categories') }}";
    const ROUTE_DELETE    = "{{ url('organization/categories') }}";

    let table, modal;

    $(document).ready(function(){

        modal = new bootstrap.Modal(document.getElementById('orgCategoryModal'));

        table = $('#orgCategoryTable').DataTable({
            processing:true,
            serverSide:true,
            ajax: ROUTE_DATATABLE,
            columns:[
                {data:'DT_RowIndex', orderable:false, searchable:false},
                {data:'name', name:'name'},
                {data:'description', name:'description', orderable:false},
                {data:'is_active', orderable:false, searchable:false}, 
                {data:'action', orderable:false, searchable:false},
            ]
        });

        $('#btnOpenCreate').on('click', function(){
            clearForm();
            $('#modalTitle').text('Add Category');
            modal.show();
        });

        $('#btnSave').on('click', function(e){
            e.preventDefault();

            const id = $('#category_id').val();
            let payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: $('#name').val(),
                description: $('#description').val(),
                is_active: $('#is_active').is(':checked') ? 1 : 0
            };

            if(!id){
                $.post(ROUTE_STORE, payload)
                    .done(res => { Swal.fire('Success', res.message ?? 'Created', 'success'); modal.hide(); table.ajax.reload(null,false); })
                    .fail(xhr => showAjaxError(xhr));
                return;
            }

            payload._method = 'PUT';
            $.post(ROUTE_UPDATE+'/'+id, payload)
                .done(res => { Swal.fire('Success', res.message ?? 'Updated', 'success'); modal.hide(); table.ajax.reload(null,false); })
                .fail(xhr => showAjaxError(xhr));
        });

        $(document).on('click', '.btn-edit', function(){
            const id = $(this).data('id');
            $.get(ROUTE_SHOW+'/'+id, function(res){
                clearForm();
                $('#modalTitle').text('Edit Category');
                $('#category_id').val(res.data.id);
                $('#name').val(res.data.name);
                $('#description').val(res.data.description);
                $('#is_active').prop('checked', !!res.data.is_active);
                modal.show();
            });
        });

        $(document).on('click', '.btn-delete', function(){
            const id = $(this).data('id');
            Swal.fire({
                title:'Are you sure?',
                text:'This will be deleted permanently!',
                icon:'warning',
                showCancelButton:true,
                confirmButtonText:'Yes, delete it!',
                cancelButtonText:'Cancel'
            }).then((r)=>{
                if(!r.isConfirmed) return;

                $.post(ROUTE_DELETE+'/'+id, {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                })
                .done(res => { Swal.fire('Deleted', res.message ?? 'Deleted', 'success'); table.ajax.reload(null,false); })
                .fail(xhr => showAjaxError(xhr));
            });
        });

    });

    function clearForm(){
        $('#category_id').val('');
        $('#name').val('');
        $('#description').val('');
        $('#is_active').prop('checked', true);
    }

    function showAjaxError(xhr){
        let msg = 'Something went wrong';
        if(xhr.status === 422 && xhr.responseJSON){
            msg = xhr.responseJSON.message ?? msg;
            if(xhr.responseJSON.errors){
                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                msg = xhr.responseJSON.errors[firstKey][0];
            }
        } else if(xhr.status === 419){
            msg = 'CSRF token mismatch (419)';
        }
        Swal.fire('Error', msg, 'error');
    }
    </script>
@endpush
