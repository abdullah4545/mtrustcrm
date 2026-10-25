@extends('backend.master')
@section('title', 'Catalogue')
@section('maincontent')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Catalogue</h5></div>
            <ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li><li class="breadcrumb-item">Products Catalogue</li></ul>
        </div>
        <div class="page-header-right ms-auto"><button type="button" class="btn btn-primary" id="catalogueCreate"><i class="feather-plus me-2"></i>Add Catalogue</button></div>
    </div>
    <div class="main-content">
        <div class="card">
            <div class="card-header"><div><h5 class="mb-1">Product catalogues</h5><small class="text-muted">Browse image and PDF catalogues by category.</small></div></div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-5"><label class="form-label" for="catalogueSearch">Search</label><input id="catalogueSearch" type="search" class="form-control" placeholder="Search title, category or description..."></div>
                    <div class="col-md-4"><label class="form-label" for="catalogueCategoryFilter">Category</label><select id="catalogueCategoryFilter" class="form-control"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label" for="catalogueStatusFilter">Status</label><select id="catalogueStatusFilter" class="form-control"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="table-responsive"><table class="table table-bordered align-middle" id="catalogueTable" style="width:100%"><thead><tr><th>SL</th><th>File</th><th>Title</th><th>Category</th><th>Description</th><th>Status</th><th>Action</th></tr></thead><tbody></tbody></table></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('modals')
<div class="modal fade" id="catalogueModal" tabindex="-1" aria-labelledby="catalogueModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="catalogueModalTitle">Add Catalogue</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="catalogueForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <input type="hidden" id="catalogueId">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label" for="catalogueTitle">Title *</label><input id="catalogueTitle" name="title" class="form-control" required maxlength="255"></div>
                    <div class="col-md-4"><label class="form-label" for="catalogueStatus">Status *</label><select id="catalogueStatus" name="status" class="form-control" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="col-12"><label class="form-label" for="catalogueCategory">Category *</label><select id="catalogueCategory" name="category_id" class="form-control" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label" for="catalogueDescription">Description</label><textarea id="catalogueDescription" name="description" class="form-control" rows="5" maxlength="50000"></textarea></div>
                    <div class="col-12"><label class="form-label" for="catalogueFile">Image or PDF <span id="catalogueFileRequired">*</span></label><input id="catalogueFile" name="file" class="form-control" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required><small class="text-muted">JPG, JPEG, PNG, WEBP or PDF. Maximum 10 MB. While editing, leave empty to keep the current file.</small><div id="cataloguePreview" class="mt-3"></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary" id="catalogueSave">Save Catalogue</button></div>
        </form>
    </div></div>
</div>
@endpush
@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    const base = @json(url('products/catalogues'));
    const modal = new bootstrap.Modal(document.getElementById('catalogueModal'));
    const form = document.getElementById('catalogueForm');
    let objectUrl = null, searchTimer = null, saving = false;
    function error(xhr){
        const d = xhr.responseJSON || {};
        let message = d.errors ? Object.values(d.errors).flat().join('\n') : d.message;
        const statusMessages = {0:'Network connection failed. Please retry.',401:'Please sign in again.',403:'You do not have Catalogue permission.',413:'The upload is larger than the server allows. Reduce the file size.',419:'Your session expired. Refresh this page and sign in again.',429:'Too many requests. Please try again shortly.',502:'The application server is unavailable.',503:'The application is temporarily unavailable.',504:'The request timed out. Check the list before retrying.'};
        message = statusMessages[xhr.status] || message || ('Request failed (HTTP '+xhr.status+'). Ask the administrator to check the server log.');
        if(d.error_reference) message += '\nError reference: '+d.error_reference;
        Swal.fire({title:'Unable to complete request',text:message,icon:'error'});
    }
    $.fn.dataTable.ext.errMode = 'none';
    const table = $('#catalogueTable').DataTable({
        processing:true, serverSide:true, pageLength:10,
        lengthMenu:[[10,25,50,100,-1],[10,25,50,100,'All']],
        dom:'lrtip', order:[[2,'asc']],
        ajax:{url:@json(route('catalogues.datatable')), headers:{Accept:'application/json'}, data:function(d){d.category_id=$('#catalogueCategoryFilter').val();d.status=$('#catalogueStatusFilter').val();}, error:error},
        columns:[
            {data:'DT_RowIndex',orderable:false,searchable:false},
            {data:'file',orderable:false,searchable:false},
            {data:'title',name:'catalogues.title'},
            {data:'category_name',name:'category.name'},
            {data:'description',name:'catalogues.description',orderable:false},
            {data:'status',name:'catalogues.status'},
            {data:'action',orderable:false,searchable:false}
        ]
    });
    $('#catalogueSearch').on('input',function(){clearTimeout(searchTimer);const value=this.value;searchTimer=setTimeout(()=>table.search(value).draw(),300);});
    $('#catalogueCategoryFilter,#catalogueStatusFilter').on('change',()=>table.ajax.reload());
    function releaseObjectUrl(){if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}}
    function preview(url,pdf){
        $('#cataloguePreview').empty();
        const link=$('<a>',{href:url,target:'_blank',rel:'noopener',class:'d-inline-block'});
        if(pdf) link.addClass('btn btn-outline-primary').text('Open PDF catalogue');
        else link.append($('<img>',{src:url,alt:'Catalogue preview',style:'max-height:200px;max-width:100%;border-radius:10px'}));
        $('#cataloguePreview').append(link);
    }
    function clearForm(){releaseObjectUrl();form.reset();$('#catalogueId').val('');$('#cataloguePreview').empty();$('#catalogueFile').prop('required',true);$('#catalogueFileRequired').show();$('#catalogueModalTitle').text('Add Catalogue');}
    $('#catalogueCreate').on('click',function(){clearForm();modal.show();});
    $('#catalogueFile').on('change',function(){
        releaseObjectUrl();$('#cataloguePreview').empty();const file=this.files[0];if(!file)return;
        if(file.size>10*1024*1024){this.value='';Swal.fire('File too large','Choose a file up to 10 MB.','error');return;}
        if(!/\.(jpe?g|png|webp|pdf)$/i.test(file.name)){this.value='';Swal.fire('Unsupported file','Choose a JPG, PNG, WEBP image or PDF.','error');return;}
        objectUrl=URL.createObjectURL(file);preview(objectUrl,/\.pdf$/i.test(file.name));
    });
    $('#catalogueForm').on('submit',function(event){
        event.preventDefault();if(saving || !form.reportValidity())return;
        const id=$('#catalogueId').val();const fd=new FormData(form);
        if(!$('#catalogueFile')[0].files.length)fd.delete('file');
        saving=true;$('#catalogueSave').prop('disabled',true).text('Saving...');
        $.ajax({url:id?base+'/'+id:base,type:'POST',data:fd,processData:false,contentType:false,headers:{Accept:'application/json'},
            success:function(res){saving=false;modal.hide();table.ajax.reload(null,false);Swal.fire('Success',res.message,'success');},error:error,
            complete:function(){saving=false;$('#catalogueSave').prop('disabled',false).text('Save Catalogue');}
        });
    });
    $('#catalogueTable').on('click','.catalogue-edit',function(){
        const button=$(this);button.prop('disabled',true);
        $.ajax({url:base+'/'+button.data('id'),headers:{Accept:'application/json'},success:function(res){
            clearForm();const d=res.data;$('#catalogueId').val(d.id);$('#catalogueTitle').val(d.title);$('#catalogueCategory').val(d.category_id);$('#catalogueDescription').val(d.description);$('#catalogueStatus').val(d.status);$('#catalogueFile').prop('required',false);$('#catalogueFileRequired').hide();$('#catalogueModalTitle').text('Edit Catalogue');preview(res.file_url,/\.pdf$/i.test(d.file_path));modal.show();
        },error:error,complete:function(){button.prop('disabled',false);}});
    });
    $('#catalogueTable').on('click','.catalogue-delete',function(){
        const button=$(this),id=button.data('id');
        Swal.fire({title:'Delete this catalogue?',text:'The catalogue and its uploaded file will be removed.',icon:'warning',showCancelButton:true,confirmButtonText:'Yes, delete',confirmButtonColor:'#dc3545'}).then(function(result){
            if(!result.isConfirmed)return;button.prop('disabled',true);
            $.ajax({url:base+'/'+id,type:'DELETE',headers:{Accept:'application/json','X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},success:function(res){table.ajax.reload(null,false);Swal.fire('Deleted',res.message,'success');},error:error,complete:function(){button.prop('disabled',false);}});
        });
    });
    document.getElementById('catalogueModal').addEventListener('hide.bs.modal',function(event){if(saving)event.preventDefault();});
    document.getElementById('catalogueModal').addEventListener('hidden.bs.modal',releaseObjectUrl);
});
</script>
@endpush
