@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Products
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
                <li class="breadcrumb-item">Manage</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Product
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                {{-- ✅ Filters (auto reload on change) --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select id="f_category" class="form-control">
                            <option value="">All Category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="f_subcategory" class="form-control">
                            <option value="">All Subcategory</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="f_brand" class="form-control">
                            <option value="">All Brand</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select id="f_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-1 d-grid">
                        <button class="btn btn-light-brand" id="btnReset">Reset</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="productTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Catalogue</th>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Subcategory</th>
                            <th>Brand</th>
                            <th>Sale</th>
                            <th>Purchase</th>
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
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="productForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="product_id">

                        <div class="row g-2">

                            <div class="col-md-4">
                                <label class="form-label">Category *</label>
                                <select id="category_id" class="form-control" required>
                                    <option value="">Select</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Subcategory</label>
                                <select id="subcategory_id" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <select id="brand_id" class="form-control">
                                    <option value="">Select</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">SKU</label>
                                <input type="text" id="sku" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Name *</label>
                                <input type="text" id="name" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Status *</label>
                                <select id="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Configuration Description</label>
                                <textarea id="configuration_description" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Sale Price *</label>
                                <input type="number" step="0.01" id="sale_price" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Purchase Price *</label>
                                <input type="number" step="0.01" id="purchase_price" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">VAT Rate (%)</label>
                                <input type="number" step="0.01" id="vat_rate" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" step="0.01" id="tax_rate" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Warranty Months</label>
                                <input type="number" id="warranty_months" class="form-control">
                            </div>

                            <div class="col-md-9">
                                <label class="form-label">Warranty Terms Details</label>
                                <textarea id="warranty_terms_details" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Product Image</label>
                                <input type="file" id="image" class="form-control" accept="image/*">
                                <div id="imgPreview" class="mt-2"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Product Catalogue (Image or PDF)</label>
                                <input type="file" id="catalogue" class="form-control" accept="image/*,application/pdf">
                                <small class="text-muted">JPG, PNG, WEBP or PDF - max 10 MB.</small>
                                <div id="cataloguePreview" class="mt-2"></div>
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
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
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

const ROUTE_DATATABLE = "{{ route('products.datatable') }}";
const ROUTE_STORE     = "{{ route('products.store') }}";
const ROUTE_SHOW      = "{{ url('products/manage') }}";
const ROUTE_UPDATE    = "{{ url('products/manage') }}";
const ROUTE_DELETE    = "{{ url('products/manage') }}";
const ROUTE_SUBCATS   = "{{ route('product.subcategory') }}";

let table, modal;
const productEditors = {};

function initProductEditors(){
    ['description','configuration_description','warranty_terms_details'].forEach(id => {
        const el = document.getElementById(id);
        if(!el || productEditors[id]) return;
        ClassicEditor.create(el, { toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo'] })
            .then(editor => { productEditors[id] = editor; })
            .catch(console.error);
    });
}

function editorData(id){
    return productEditors[id] ? productEditors[id].getData() : ($('#'+id).val() || '');
}

function setEditorData(id, value){
    if(productEditors[id]) productEditors[id].setData(value || '');
    else $('#'+id).val(value || '');
}

$(document).ready(function(){
    initProductEditors();
    modal = new bootstrap.Modal(document.getElementById('productModal'));

    table = $('#productTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){
                d.category_id = $('#f_category').val();
                d.subcategory_id = $('#f_subcategory').val();
                d.brand_id = $('#f_brand').val();
                d.status = $('#f_status').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'image', orderable:false, searchable:false},
            {data:'catalogue', orderable:false, searchable:false},
            {data:'sku', name:'sku'},
            {data:'name', name:'name'},
            {data:'cat', orderable:false, searchable:false},
            {data:'subcat', orderable:false, searchable:false},
            {data:'brand', orderable:false, searchable:false},
            {data:'sale_price', name:'sale_price'},
            {data:'purchase_price', name:'purchase_price'},
            {data:'status', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    // ✅ AUTO filter reload (no Filter button)
    $('#f_category,#f_subcategory,#f_brand,#f_status').on('change', function(){
        table.ajax.reload();
    });

    // ✅ reset
    $('#btnReset').on('click', function(){
        $('#f_category,#f_brand,#f_status').val('');
        $('#f_subcategory').html('<option value="">All Subcategory</option>');
        table.ajax.reload();
    });

    // ✅ filter category -> subcategory
    $('#f_category').on('change', function(){
        $('#f_subcategory').html('<option value="">All Subcategory</option>');
        const category_id = $(this).val();
        if(!category_id) return;

        $.get(ROUTE_SUBCATS, {category_id}).then(res=>{
            let html = '<option value="">All Subcategory</option>';
            res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
            $('#f_subcategory').html(html);
        });
    });

    // ✅ open create
    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Product');
        modal.show();
    });

    // ✅ modal category -> subcategory
    $('#category_id').on('change', function(){
        $('#subcategory_id').html('<option value="">Select</option>');
        const category_id = $(this).val();
        if(!category_id) return;

        $.get(ROUTE_SUBCATS, {category_id}).then(res=>{
            let html = '<option value="">Select</option>';
            res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
            $('#subcategory_id').html(html);
        });
    });

    // ✅ save (create/update)
    $('#btnSave').on('click', function(e){
        e.preventDefault();

        const id = $('#product_id').val();
        let fd = new FormData();

        fd.append('category_id', $('#category_id').val());
        fd.append('subcategory_id', $('#subcategory_id').val());
        fd.append('brand_id', $('#brand_id').val());

        fd.append('sku', $('#sku').val());
        fd.append('name', $('#name').val());
        fd.append('description', editorData('description'));

        fd.append('sale_price', $('#sale_price').val());
        fd.append('purchase_price', $('#purchase_price').val());
        fd.append('vat_rate', $('#vat_rate').val());
        fd.append('tax_rate', $('#tax_rate').val());

        fd.append('warranty_months', $('#warranty_months').val());
        fd.append('warranty_terms_details', editorData('warranty_terms_details'));
        fd.append('configuration_description', editorData('configuration_description'));

        fd.append('status', $('#status').val());

        if($('#image')[0].files[0]) fd.append('image', $('#image')[0].files[0]);
        if($('#catalogue')[0].files[0]) fd.append('catalogue', $('#catalogue')[0].files[0]);

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
            url: ROUTE_UPDATE+'/'+id,
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

    // ✅ edit
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');

        $.get(ROUTE_SHOW+'/'+id, async function(res){
            clearForm();
            const d = res.data;

            $('#modalTitle').text('Edit Product');
            $('#product_id').val(d.id);

            $('#category_id').val(d.category_id).trigger('change');

            // load subcats then set value
            await loadSubcatsTo('#category_id', '#subcategory_id', d.subcategory_id);

            $('#brand_id').val(d.brand_id);
            $('#sku').val(d.sku);
            $('#name').val(d.name);
            setEditorData('description', d.description);

            $('#sale_price').val(d.sale_price);
            $('#purchase_price').val(d.purchase_price);
            $('#vat_rate').val(d.vat_rate);
            $('#tax_rate').val(d.tax_rate);

            $('#warranty_months').val(d.warranty_months);
            setEditorData('warranty_terms_details', d.warranty_terms_details);
            setEditorData('configuration_description', d.configuration_description);

            $('#status').val(d.status);

            if(d.image_url){
                $('#imgPreview').html(`<img src="{{ asset('') }}${d.image_url}" style="height:60px;border-radius:8px;">`);
            }
            if(d.catalogue_file){
                const viewUrl = `{{ url('products/manage') }}/${d.id}/catalogue/view`;
                const downloadUrl = `{{ url('products/manage') }}/${d.id}/catalogue/download`;
                $('#cataloguePreview').html(`<a href="${viewUrl}" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="feather-eye"></i> View</a><a href="${downloadUrl}" class="btn btn-sm btn-outline-success"><i class="feather-download"></i> Download</a>`);
            }

            modal.show();
        });
    });

    // ✅ delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This product will be deleted!',
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
    $('#product_id').val('');
    document.getElementById('productForm').reset();
    $('#subcategory_id').html('<option value="">Select</option>');
    $('#imgPreview,#cataloguePreview').html('');
    setEditorData('description','');
    setEditorData('configuration_description','');
    setEditorData('warranty_terms_details','');
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

function loadSubcatsTo(categorySel, subcatSel, selectedId=null){
    const category_id = $(categorySel).val();
    if(!category_id) return Promise.resolve();

    return $.get(ROUTE_SUBCATS, {category_id}).then(res=>{
        let html = '<option value="">Select</option>';
        res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
        $(subcatSel).html(html);
        if(selectedId) $(subcatSel).val(selectedId);
    });
}
</script>
@endpush
