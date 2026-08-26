@extends('backend.master')
@section('title','Users & Work Areas')
@section('maincontent')
<style>
.area-card{border:1px solid #e8edf4;border-radius:14px;padding:14px;background:#fbfcfe}.area-row{border:1px solid #e6ebf2;border-radius:12px;padding:12px;background:#fff}.area-summary{font-size:12px;line-height:1.55}.user-head{display:flex;justify-content:space-between;align-items:center;gap:12px}.touch-btn{min-height:44px}
@media(max-width:767.98px){.user-head{align-items:flex-start;flex-direction:column}.user-head .btn{width:100%}.modal-dialog{margin:0;max-width:none;height:100%}.modal-content{min-height:100vh;border-radius:0}.modal-footer{position:sticky;bottom:0;background:white;z-index:4}.dataTables_wrapper .dataTables_filter input{width:180px!important}.card-body{padding:14px}.area-row .form-select{min-height:46px}.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch}}
</style>
<div class="nxl-content">
  <div class="page-header"><div class="user-head w-100"><div><h4 class="mb-1">Users & Work Areas</h4></div><button class="btn btn-primary touch-btn" id="btnOpenCreate"><i class="feather-user-plus me-2"></i>Add User</button></div></div>
  <div class="main-content">
    <div class="card mb-3"><div class="card-body"><div class="row g-2">
      <div class="col-md-4"><label class="form-label">Branch</label><select id="filter_branch_id" class="form-select"><option value="">All Branches</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->branch_name }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">Role</label><select id="filter_role" class="form-select"><option value="">All Roles</option>@foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">Status</label><select id="filter_status" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
    </div></div></div>
    <div class="card"><div class="card-body"><div class="table-responsive"><table id="userTable" class="table table-hover align-middle w-100"><thead><tr><th>#</th><th>Photo</th><th>User</th><th>Phone</th><th>Branch</th><th>Role</th><th>Assigned Work Area</th><th>Status</th><th>Action</th></tr></thead></table></div></div></div>
  </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="userModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
<div class="modal-header"><div><h5 class="modal-title" id="modalTitle">Add User</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="userForm" enctype="multipart/form-data">@csrf<input type="hidden" id="user_id">
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Name *</label><input id="name" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Phone</label><input id="phone" class="form-control" inputmode="tel"></div>
<div class="col-md-4"><label class="form-label">Email *</label><input id="email" type="email" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Branch *</label><select id="branch_id" class="form-select" required><option value="">Select Branch</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->branch_name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Role *</label><select id="role" class="form-select" required><option value="">Select Role</option>@foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Status</label><select id="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
<div class="col-md-2"><label class="form-label">Join Date</label><input id="join_date" type="date" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Present Address</label><input id="present_address" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Permanent Address</label><input id="parmanent_address" class="form-control"></div>

<div class="col-12" id="workAreaSection" style="display:none"><div class="area-card">
<div class="d-flex justify-content-between align-items-center gap-2 mb-2"><div><h6 class="mb-0">Staff Work Areas *</h6></div><button type="button" class="btn btn-sm btn-outline-primary touch-btn" id="addArea"><i class="feather-plus"></i> Add District</button></div>
<div id="areaRows" class="d-grid gap-2"></div>
<div class="alert alert-info py-2 mt-3 mb-0"><b>Example:</b> Dhaka = All Upazilas; Gazipur = Sreepur + Kaliakair. Staff will only see organizations in those addresses.</div>
</div></div>

<div class="col-md-4"><label class="form-label">Profile Photo</label><input id="profile" type="file" class="form-control" accept="image/*"><div id="imgPreview" class="mt-2"></div></div>
<div class="col-md-4"><label class="form-label">Password <span id="passHint" class="text-muted"></span></label><input id="password" type="password" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Confirm Password</label><input id="password_confirmation" type="password" class="form-control"></div>
</div></form></div>
<div class="modal-footer"><button class="btn btn-light touch-btn" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-primary touch-btn" id="btnSave"><i class="feather-save me-1"></i> Save User</button></div>
</div></div></div>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
const URL_BASE="{{ url('system/users') }}", URL_STORE="{{ route('users.store') }}", URL_DT="{{ route('users.datatable') }}", URL_UPA="{{ url('/geo/upazilas') }}";
const districtOptions=`<option value="">Select District</option>@foreach($districts as $d)<option value="{{ $d->id }}">{{ addslashes($d->name) }}</option>@endforeach`;
let table,modal,areaSeq=0;
$(function(){
 modal=new bootstrap.Modal('#userModal');
 table=$('#userTable').DataTable({processing:true,serverSide:true,pageLength:20,ajax:{url:URL_DT,data:d=>{d.branch_id=$('#filter_branch_id').val();d.role=$('#filter_role').val();d.status=$('#filter_status').val()}},columns:[
 {data:'DT_RowIndex',orderable:false,searchable:false},{data:'profile',orderable:false,searchable:false},{data:'name'},{data:'phone'},{data:'branch',orderable:false},{data:'role',orderable:false},{data:'areas',orderable:false,searchable:false},{data:'status',orderable:false},{data:'action',orderable:false,searchable:false}
 ]});
 $('#filter_branch_id,#filter_role,#filter_status').change(()=>table.ajax.reload());
 $('#role').change(toggleAreas);
 $('#addArea').click(()=>addAreaRow());
 $('#btnOpenCreate').click(()=>{clearForm();$('#modalTitle').text('Add User');$('#passHint').text('(required)');modal.show()});
 $('#btnSave').click(saveUser);
 $(document).on('click','.remove-area',function(){$(this).closest('.area-row').remove()});
 $(document).on('change','.area-district',function(){loadUpazilas($(this).closest('.area-row'),$(this).val(),[])});
 $(document).on('change','.area-all',function(){const row=$(this).closest('.area-row');row.find('.area-upazilas').prop('disabled',this.checked);if(this.checked)row.find('.area-upazilas').val([])});
 $(document).on('click','.btn-edit',function(){editUser($(this).data('id'))});
 $(document).on('click','.btn-delete',function(){deleteUser($(this).data('id'))});
});
function toggleAreas(){const staff=$('#role').val()==='staff';$('#workAreaSection').toggle(staff);if(staff&&!$('#areaRows .area-row').length)addAreaRow()}
function addAreaRow(area=null){const id=++areaSeq;$('#areaRows').append(`<div class="area-row" data-key="${id}"><div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">District</label><select class="form-select area-district">${districtOptions}</select></div><div class="col-md-5"><label class="form-label">Upazilas</label><select class="form-select area-upazilas" multiple size="4"></select></div><div class="col-md-2"><div class="form-check form-switch mb-2"><input class="form-check-input area-all" type="checkbox" id="all_${id}"><label class="form-check-label" for="all_${id}">All Upazilas</label></div></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-area"><i class="feather-trash-2"></i></button></div></div></div>`);const row=$(`#areaRows .area-row[data-key="${id}"]`);if(area){row.find('.area-district').val(area.district_id);loadUpazilas(row,area.district_id,area.upazila_ids||[],()=>{row.find('.area-all').prop('checked',!!area.all_upazilas).trigger('change')})}}
function loadUpazilas(row,districtId,selected=[],done=null){const sel=row.find('.area-upazilas');sel.html('');if(!districtId){if(done)done();return}$.get(URL_UPA+'/'+districtId,res=>{res.forEach(x=>sel.append(`<option value="${x.id}">${x.name}</option>`));sel.val((selected||[]).map(String));if(done)done()})}
function collectAreas(){let out=[];$('#areaRows .area-row').each(function(){const row=$(this),district=row.find('.area-district').val();if(!district)return;out.push({district_id:Number(district),all_upazilas:row.find('.area-all').is(':checked'),upazila_ids:(row.find('.area-upazilas').val()||[]).map(Number)})});return out}
function makeFd(){let fd=new FormData();['name','email','phone','branch_id','role','status','join_date','present_address','parmanent_address','password','password_confirmation'].forEach(k=>fd.append(k,$('#'+k).val()||''));fd.append('areas',JSON.stringify(collectAreas()));if($('#profile')[0].files[0])fd.append('profile',$('#profile')[0].files[0]);return fd}
function saveUser(){const id=$('#user_id').val();$('#btnSave').prop('disabled',true);$.ajax({url:id?URL_BASE+'/'+id:URL_STORE,type:'POST',data:makeFd(),processData:false,contentType:false}).done(r=>{Swal.fire('Saved',r.message||'Saved','success');modal.hide();table.ajax.reload(null,false)}).fail(showError).always(()=>$('#btnSave').prop('disabled',false))}
function editUser(id){$.get(URL_BASE+'/'+id,r=>{clearForm();const d=r.data;$('#user_id').val(d.id);$('#modalTitle').text('Edit User');$('#passHint').text('(leave blank to keep)');['name','email','phone','branch_id','status','join_date','present_address','parmanent_address'].forEach(k=>$('#'+k).val(d[k]??''));$('#role').val(r.role||'').trigger('change');$('#areaRows').html('');(r.areas||[]).forEach(a=>addAreaRow(a));if(r.role==='staff'&&!(r.areas||[]).length)addAreaRow();if(d.profile)$('#imgPreview').html(`<img src="{{ asset('') }}${d.profile}" style="height:64px;border-radius:10px">`);modal.show()})}
function clearForm(){document.getElementById('userForm').reset();$('#user_id').val('');$('#areaRows,#imgPreview').html('');$('#status').val('1');$('#workAreaSection').hide()}
function deleteUser(id){Swal.fire({title:'Delete user?',icon:'warning',showCancelButton:true,confirmButtonText:'Delete'}).then(x=>{if(x.isConfirmed)$.post(URL_BASE+'/'+id+'/delete').done(r=>{Swal.fire('Deleted',r.message,'success');table.ajax.reload(null,false)}).fail(showError)})}
function showError(xhr){let m=xhr.responseJSON?.message||'Something went wrong';if(xhr.responseJSON?.errors){m=Object.values(xhr.responseJSON.errors)[0][0]}Swal.fire('Error',m,'error')}
</script>
@endpush
