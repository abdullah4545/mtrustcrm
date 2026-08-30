@php
    $editing = isset($activity) && $activity;
    $activityTime = $editing ? ($activity->activity_at ?? $activity->created_at) : now('Asia/Dhaka');
    $travelRows = $editing && $activity->travels->count()
        ? $activity->travels
        : ($editing && ($activity->from_location || $activity->to_location || (float)$activity->ta > 0)
            ? collect([(object)[
                'from_location'=>$activity->from_location,
                'to_location'=>$activity->to_location,
                'vehicle'=>$activity->vehicle,
                'distance'=>$activity->distance,
                'cost'=>$activity->ta,
            ]])
            : collect());
    $expenseRows = $editing && $activity->expenses->count() ? $activity->expenses : collect();
    $canManageActivityEntry = $canManageActivityEntry ?? false;

    $initialTravels = $travelRows->map(fn($row) => [
        'from_location' => $row?->from_location ?? '',
        'to_location' => $row?->to_location ?? '',
        'vehicle' => $row?->vehicle ?? '',
        'distance' => (float)($row?->distance ?? 0),
        'cost' => (float)($row?->cost ?? 0),
        'image_url' => $row?->image_url ?? '',
        'image_view_url' => $row?->image_url ? asset($row->image_url) : '',
    ])->values();

    $initialExpenses = $expenseRows->map(fn($row) => [
        'expense_type_id' => (string)($row?->expense_type_id ?? ''),
        'expense_type' => $row?->expense_type ?? '',
        'amount' => (float)($row?->amount ?? 0),
        'note' => $row?->note ?? '',
        'image_url' => $row?->image_url ?? '',
        'image_view_url' => $row?->image_url ? asset($row->image_url) : '',
    ])->values();
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.activity-entry-card{border:0;border-radius:18px;box-shadow:0 10px 30px rgba(15,23,42,.07)}
.activity-entry-card label{font-weight:600;margin-bottom:6px}
.activity-section{border:1px solid #e8edf3;border-radius:16px;background:#fff;overflow:hidden}
.activity-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #eef2f6;background:#fafbfc}
.activity-section-body{padding:12px}
.activity-empty{padding:24px 12px;text-align:center;color:#98a2b3;border:1px dashed #dbe2ea;border-radius:12px;background:#fbfcfd}
.activity-list-item{display:flex;gap:12px;align-items:center;padding:12px;border:1px solid #edf1f5;border-radius:13px;background:#fff;margin-bottom:10px}
.activity-list-item:last-child{margin-bottom:0}
.activity-list-main{min-width:0;flex:1}
.activity-list-title{font-weight:700;color:#1f2937;word-break:break-word}
.activity-list-meta{display:flex;flex-wrap:wrap;gap:6px 12px;margin-top:4px;color:#667085;font-size:12px}
.activity-list-amount{font-weight:700;white-space:nowrap;color:#111827}
.activity-list-actions{display:flex;gap:6px;flex:0 0 auto}
.icon-action{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;padding:0}
.summary-box{background:#f8fafc;border-radius:14px;padding:16px}
.select2-container{width:100%!important}.select2-container .select2-selection--single{height:40px!important;border:1px solid #ced4da!important;border-radius:.375rem!important}.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:38px!important}.select2-container--default .select2-selection--single .select2-selection__arrow{height:38px!important}
.modal-backdrop{z-index:1050!important;backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);background-color:rgba(15,23,42,.38)!important}
.modal-backdrop.show{opacity:1!important}
.activity-modal{z-index:1060!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
.activity-modal .modal-dialog{position:relative;z-index:1061;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
.activity-modal .modal-content{position:relative;z-index:1062;border:0;border-radius:18px;background:#fff!important;opacity:1!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;box-shadow:0 18px 50px rgba(15,23,42,.2)}
.activity-modal .modal-header{border-bottom:1px solid #eef2f6}.activity-modal .modal-footer{border-top:1px solid #eef2f6}
.mobile-add-btn{white-space:nowrap}
@media(max-width:767.98px){
    .nxl-content .main-content{padding-left:12px!important;padding-right:12px!important}
    .activity-entry-card .card-body{padding:12px}
    .activity-entry-card .row.g-3{--bs-gutter-x:.75rem;--bs-gutter-y:.75rem}
    .activity-section-head{padding:12px;align-items:flex-start}
    .activity-section-head small{font-size:11px;line-height:1.4;display:block;padding-right:4px}
    .activity-section-body{padding:10px}
    .activity-list-item{align-items:flex-start;gap:8px;padding:10px}
    .activity-list-amount{font-size:13px}
    .activity-list-actions{gap:4px}
    .icon-action{width:32px;height:32px}
    .mobile-add-btn{padding:.45rem .6rem;font-size:12px}
    .summary-box{padding:12px 6px}
    .summary-box h5{font-size:16px}
    .activity-save{width:100%;padding:.7rem 1rem}
    .activity-modal .modal-dialog{margin:8px}
    .activity-modal .modal-content{border-radius:15px}
    .activity-modal .modal-body{padding:14px}
    .activity-modal .modal-footer{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px}
    .activity-modal .modal-footer .btn{margin:0;width:100%}
}
@media(max-width:420px){
    .activity-section-head{gap:8px}
    .activity-section-head h6{font-size:14px}
    .activity-list-item{display:grid;grid-template-columns:minmax(0,1fr) auto}
    .activity-list-main{grid-column:1/2}
    .activity-list-amount{grid-column:2/3;grid-row:1}
    .activity-list-actions{grid-column:1/3;justify-content:flex-end;margin-top:2px}
}
</style>

<div class="card activity-entry-card"><div class="card-body">
<form id="activityForm">
<div class="row g-3">
    <div class="col-12 col-md-4">
        <label>Activity Date & Time</label>
        @if($canManageActivityEntry)
            <input type="datetime-local" id="activity_at" class="form-control" value="{{ $activityTime?->timezone('Asia/Dhaka')->format('Y-m-d\TH:i') }}">
        @else
            <input class="form-control" value="{{ $activityTime?->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}" readonly>
        @endif
    </div>

    @if($canManageActivityEntry)
    <div class="col-12 col-md-4">
        <label>Staff</label>
        <select id="staff_id" class="form-control">
            <option value="">Myself</option>
            @foreach($staffs as $staff)
                <option value="{{ $staff->id }}" {{ $editing && (int)$activity->created_by === (int)$staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="col-12 col-md-4"><label>Organization *</label><select id="organization_id" class="form-control" required><option value="">Select Organization</option></select></div>
    <div class="col-12 col-md-4"><label>Department *</label><select id="department" class="form-control" required><option value="">Select Department</option></select></div>
    <div class="col-12 col-md-4"><label>Contact Person</label><select id="contact_id" class="form-control"><option value="">Select Contact</option></select></div>
    @if($isAdmin)<div class="col-12 col-md-4"><label>Status</label><select id="status" class="form-control"><option value="pending" {{ $editing&&$activity->status==='pending'?'selected':'' }}>Pending</option><option value="approved" {{ $editing&&$activity->status==='approved'?'selected':'' }}>Approved</option><option value="rejected" {{ $editing&&$activity->status==='rejected'?'selected':'' }}>Rejected</option></select></div>@endif

    <div class="col-12">
        <div class="activity-section">
            <div class="activity-section-head">
                <div><h6 class="mb-0">TA / Travel</h6></div>
                <button type="button" class="btn btn-sm btn-primary mobile-add-btn" id="addTravel"><i class="feather-plus me-1"></i>Add Travel</button>
            </div>
            <div class="activity-section-body" id="travelList"></div>
        </div>
    </div>

    <div class="col-12">
        <div class="activity-section">
            <div class="activity-section-head">
                <div><h6 class="mb-0">DA / Other Cost</h6></div>
                <button type="button" class="btn btn-sm btn-primary mobile-add-btn" id="addExpense"><i class="feather-plus me-1"></i>Add Cost</button>
            </div>
            <div class="activity-section-body" id="expenseList"></div>
        </div>
    </div>

    <div class="col-12"><label>Visit Output</label><textarea id="work_details" class="form-control" rows="4">{{ $editing?$activity->work_details:'' }}</textarea></div>
    <div class="col-12"><label>Remarks</label><textarea id="remarks" class="form-control" rows="3">{{ $editing?$activity->remarks:'' }}</textarea></div>

    <div class="col-12"><div class="summary-box row g-2 text-center"><div class="col-4"><small>Total TA</small><h5 class="mb-0" id="taText">0.00</h5></div><div class="col-4"><small>Total DA</small><h5 class="mb-0" id="daText">0.00</h5></div><div class="col-4"><small>Grand Total</small><h5 class="mb-0" id="totalText">0.00</h5></div></div></div>
    <div class="col-12 text-end"><button type="button" class="btn btn-primary activity-save" id="btnSave">{{ $editing?'Update Activity':'Save Activity' }}</button></div>
</div>
</form>
</div></div>

<div class="modal fade activity-modal" id="travelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="travelModalTitle">Add Travel</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <input type="hidden" id="travelEditIndex" value="">
                <div class="row g-3">
                    <div class="col-12"><label>From *</label><input id="travel_from" class="form-control" placeholder="From location"></div>
                    <div class="col-12"><label>To *</label><input id="travel_to" class="form-control" placeholder="To location"></div>
                    <div class="col-12 col-sm-6"><label>Vehicle</label><select id="travel_vehicle" class="form-control"><option value="">Select Vehicle</option></select></div>
                    <div class="col-6 col-sm-3"><label>Distance (KM)</label><input type="number" min="0" step="0.01" id="travel_distance" class="form-control" value="0"></div>
                    <div class="col-6 col-sm-3"><label>Cost *</label><input type="number" min="0" step="0.01" id="travel_cost" class="form-control" value="0"></div>
                    <div class="col-12"><label>TA Image</label><input type="file" accept="image/*" id="travel_image" class="form-control"><small class="text-muted" id="travel_image_hint">JPG, PNG or WEBP - max 5 MB.</small></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="saveTravelRow">Save Travel</button></div>
        </div>
    </div>
</div>

<div class="modal fade activity-modal" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="expenseModalTitle">Add Cost</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <input type="hidden" id="expenseEditIndex" value="">
                <div class="row g-3">
                    <div class="col-12"><label>Expense Type *</label><select id="expense_type" class="form-control"><option value="">Select Type</option>@foreach($expenseTypes as $type)<option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>@endforeach</select></div>
                    <div class="col-12"><label>Amount *</label><input type="number" min="0" step="0.01" id="expense_amount" class="form-control" value="0"></div>
                    <div class="col-12"><label>Note</label><input id="expense_note" class="form-control" placeholder="Optional note"></div>
                    <div class="col-12"><label>DA / Cost Image</label><input type="file" accept="image/*" id="expense_image" class="form-control"><small class="text-muted" id="expense_image_hint">JPG, PNG or WEBP - max 5 MB.</small></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="saveExpenseRow">Save Cost</button></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});

const ACTIVITY_URL = @json($editing ? route('activities.update',$activity->id) : route('activities.quick.store'));
const ORG_URL = @json(url('activities/ajax/organizations'));
const VEHICLE_URL = @json(url('activities/ajax/vehicles'));
const DEPT_URL = @json(url('activities/ajax/org-departments'));
const CONTACT_URL = @json(url('activities/ajax/org-contacts'));
const OLD_ORG = @json($editing ? (string)$activity->organization_id : '');
const OLD_DEPT = @json($editing ? (string)$activity->department_id : '');
const OLD_CONTACT = @json($editing ? (string)$activity->contact_id : '');
const CAN_MANAGE_ENTRY = @json((bool)$canManageActivityEntry);
let travels = @json($initialTravels);
let expenses = @json($initialExpenses);
let vehicles = [];
let travelModal, expenseModal;

function esc(v){ return $('<div>').text(v ?? '').html(); }
function money(v){ return (parseFloat(v)||0).toFixed(2); }

function recalc(){
    const ta = travels.reduce((s,r)=>s+(parseFloat(r.cost)||0),0);
    const da = expenses.reduce((s,r)=>s+(parseFloat(r.amount)||0),0);
    $('#taText').text(ta.toFixed(2));
    $('#daText').text(da.toFixed(2));
    $('#totalText').text((ta+da).toFixed(2));
}

function renderTravels(){
    const box = $('#travelList');
    if(!travels.length){
        box.html('<div class="activity-empty"><i class="feather-navigation d-block mb-2" style="font-size:22px"></i>No travel added yet.</div>');
        recalc(); return;
    }
    let html='';
    travels.forEach((r,i)=>{
        html += `<div class="activity-list-item">
            <div class="activity-list-main">
                <div class="activity-list-title">${esc(r.from_location || '—')} <i class="feather-arrow-right mx-1"></i> ${esc(r.to_location || '—')}</div>
                <div class="activity-list-meta">
                    <span><i class="feather-truck me-1"></i>${esc(r.vehicle || 'No vehicle')}</span>
                    <span><i class="feather-map-pin me-1"></i>${money(r.distance)} KM</span>
                </div>
            </div>
            <div class="activity-list-amount">৳${money(r.cost)}</div>
            <div class="activity-list-actions">
                ${(r.image_preview_url || r.image_view_url) ? `<a href="${esc(r.image_preview_url || r.image_view_url)}" target="_blank" class="btn btn-outline-success icon-action" title="View Image"><i class="feather-image"></i></a>` : ''}
                <button type="button" class="btn btn-outline-primary icon-action edit-travel" data-index="${i}" title="Edit"><i class="feather-edit-2"></i></button>
                <button type="button" class="btn btn-outline-danger icon-action delete-travel" data-index="${i}" title="Delete"><i class="feather-trash-2"></i></button>
            </div>
        </div>`;
    });
    box.html(html); recalc();
}

function renderExpenses(){
    const box = $('#expenseList');
    if(!expenses.length){
        box.html('<div class="activity-empty"><i class="feather-credit-card d-block mb-2" style="font-size:22px"></i>No other cost added yet.</div>');
        recalc(); return;
    }
    let html='';
    expenses.forEach((r,i)=>{
        html += `<div class="activity-list-item">
            <div class="activity-list-main">
                <div class="activity-list-title">${esc(r.expense_type || 'Other Cost')}</div>
                <div class="activity-list-meta">${r.note ? `<span><i class="feather-file-text me-1"></i>${esc(r.note)}</span>` : '<span>No note</span>'}</div>
            </div>
            <div class="activity-list-amount">৳${money(r.amount)}</div>
            <div class="activity-list-actions">
                ${(r.image_preview_url || r.image_view_url) ? `<a href="${esc(r.image_preview_url || r.image_view_url)}" target="_blank" class="btn btn-outline-success icon-action" title="View Image"><i class="feather-image"></i></a>` : ''}
                <button type="button" class="btn btn-outline-primary icon-action edit-expense" data-index="${i}" title="Edit"><i class="feather-edit-2"></i></button>
                <button type="button" class="btn btn-outline-danger icon-action delete-expense" data-index="${i}" title="Delete"><i class="feather-trash-2"></i></button>
            </div>
        </div>`;
    });
    box.html(html); recalc();
}

function fillVehicleOptions(selected=''){
    let html='<option value="">Select Vehicle</option>';
    vehicles.forEach(v=>{ html += `<option value="${esc(v.title)}" ${v.title===selected?'selected':''}>${esc(v.title)}</option>`; });
    $('#travel_vehicle').html(html);
}

function resetTravelModal(){
    $('#travelEditIndex').val(''); $('#travelModalTitle').text('Add Travel');
    $('#travel_from,#travel_to').val(''); $('#travel_distance,#travel_cost').val(0); $('#travel_image').val(''); $('#travel_image_hint').text('JPG, PNG or WEBP - max 5 MB.'); fillVehicleOptions('');
}
function openTravelEdit(index){
    const r=travels[index]; if(!r) return;
    $('#travelEditIndex').val(index); $('#travelModalTitle').text('Edit Travel');
    $('#travel_from').val(r.from_location); $('#travel_to').val(r.to_location); fillVehicleOptions(r.vehicle); $('#travel_distance').val(r.distance); $('#travel_cost').val(r.cost); $('#travel_image').val('');
    $('#travel_image_hint').text((r.image_file?.name || (r.image_url ? 'Current image attached. Choose a file only to replace it.' : 'JPG, PNG or WEBP - max 5 MB.')));
    travelModal.show();
}

function resetExpenseModal(){
    $('#expenseEditIndex').val(''); $('#expenseModalTitle').text('Add Cost');
    $('#expense_type').val(''); $('#expense_amount').val(0); $('#expense_note').val(''); $('#expense_image').val(''); $('#expense_image_hint').text('JPG, PNG or WEBP - max 5 MB.');
}
function openExpenseEdit(index){
    const r=expenses[index]; if(!r) return;
    $('#expenseEditIndex').val(index); $('#expenseModalTitle').text('Edit Cost');
    $('#expense_type').val(String(r.expense_type_id||'')); $('#expense_amount').val(r.amount); $('#expense_note').val(r.note||''); $('#expense_image').val('');
    $('#expense_image_hint').text((r.image_file?.name || (r.image_url ? 'Current image attached. Choose a file only to replace it.' : 'JPG, PNG or WEBP - max 5 MB.')));
    expenseModal.show();
}

function loadDepartments(org,selected='',contact=''){
    $.get(DEPT_URL+'/'+org,r=>{
        let h='<option value="">Select Department</option>';
        r.forEach(v=>h+=`<option value="${v.id}" data-title="${esc(v.title)}" ${String(v.id)===String(selected)?'selected':''}>${esc(v.title)}</option>`);
        $('#department').html(h).trigger('change.select2');
        if(selected) loadContacts(org,selected,contact);
    });
}
function loadContacts(org,dept,selected=''){
    $.get(CONTACT_URL+'/'+org+'/'+dept,r=>{
        let h='<option value="">Select Contact</option>';
        r.forEach(v=>h+=`<option value="${v.id}" data-name="${esc(v.name)}" ${String(v.id)===String(selected)?'selected':''}>${esc(v.name)}${v.designation?' - '+esc(v.designation.title):''}</option>`);
        $('#contact_id').html(h).trigger('change.select2');
    });
}

$(function(){
    // Move modals to <body> so parent theme transforms/filters cannot blur or block them.
    ['travelModal','expenseModal'].forEach(function(id){
        const el = document.getElementById(id);
        if(el && el.parentElement !== document.body){ document.body.appendChild(el); }
    });

    travelModal = new bootstrap.Modal(document.getElementById('travelModal'), {backdrop:true, keyboard:true, focus:true});
    expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'), {backdrop:true, keyboard:true, focus:true});

    $('#organization_id,#department,#contact_id,#status,#staff_id').each(function(){ if(this) $(this).select2({width:'100%'}); });

    $.get(VEHICLE_URL,r=>{ vehicles=r; fillVehicleOptions(''); });
    $.get(ORG_URL,r=>{
        let h='<option value="">Select Organization</option>';
        r.forEach(v=>h+=`<option value="${v.id}" ${String(v.id)===OLD_ORG?'selected':''}>${esc(v.name)}</option>`);
        $('#organization_id').html(h).trigger('change.select2');
        if(OLD_ORG) loadDepartments(OLD_ORG,OLD_DEPT,OLD_CONTACT);
    });

    renderTravels(); renderExpenses();
});

$('#organization_id').on('change',function(){
    const v=$(this).val();
    $('#department').html('<option value="">Select Department</option>').trigger('change.select2');
    $('#contact_id').html('<option value="">Select Contact</option>').trigger('change.select2');
    if(v) loadDepartments(v);
});
$('#department').on('change',function(){
    const org=$('#organization_id').val(), d=$(this).val();
    $('#contact_id').html('<option value="">Select Contact</option>').trigger('change.select2');
    if(org&&d) loadContacts(org,d);
});

$('#addTravel').on('click',function(){ resetTravelModal(); travelModal.show(); });
$('#saveTravelRow').on('click',function(){
    const from=$.trim($('#travel_from').val()), to=$.trim($('#travel_to').val()), cost=parseFloat($('#travel_cost').val())||0;
    if(!from || !to){ Swal.fire('Required','From and To location are required.','warning'); return; }
    if(cost < 0){ Swal.fire('Invalid','Travel cost cannot be negative.','warning'); return; }
    const idx=$('#travelEditIndex').val();
    const oldRow = idx==='' ? {} : (travels[parseInt(idx,10)] || {});
    const imageFile = document.getElementById('travel_image').files[0] || oldRow.image_file || null;
    const row={from_location:from,to_location:to,vehicle:$('#travel_vehicle').val()||'',distance:parseFloat($('#travel_distance').val())||0,cost:cost,image_url:oldRow.image_url||'',image_view_url:oldRow.image_view_url||'',image_file:imageFile,image_preview_url:imageFile ? (oldRow.image_file===imageFile && oldRow.image_preview_url ? oldRow.image_preview_url : URL.createObjectURL(imageFile)) : ''};
    if(idx==='') travels.push(row); else travels[parseInt(idx,10)]=row;
    renderTravels(); travelModal.hide();
});
$(document).on('click','.edit-travel',function(){ openTravelEdit(parseInt($(this).data('index'),10)); });
$(document).on('click','.delete-travel',function(){
    const idx=parseInt($(this).data('index'),10);
    Swal.fire({title:'Delete this travel?',text:'This row will be removed from the activity.',icon:'warning',showCancelButton:true,confirmButtonText:'Delete'}).then(r=>{ if(r.isConfirmed){ travels.splice(idx,1); renderTravels(); } });
});

$('#addExpense').on('click',function(){ resetExpenseModal(); expenseModal.show(); });
$('#saveExpenseRow').on('click',function(){
    const typeId=$('#expense_type').val(), amount=parseFloat($('#expense_amount').val())||0;
    if(!typeId){ Swal.fire('Required','Please select an expense type.','warning'); return; }
    if(amount < 0){ Swal.fire('Invalid','Amount cannot be negative.','warning'); return; }
    const idx=$('#expenseEditIndex').val();
    const oldRow = idx==='' ? {} : (expenses[parseInt(idx,10)] || {});
    const imageFile = document.getElementById('expense_image').files[0] || oldRow.image_file || null;
    const row={expense_type_id:String(typeId),expense_type:$('#expense_type option:selected').data('name')||$('#expense_type option:selected').text(),amount:amount,note:$.trim($('#expense_note').val()),image_url:oldRow.image_url||'',image_view_url:oldRow.image_view_url||'',image_file:imageFile,image_preview_url:imageFile ? (oldRow.image_file===imageFile && oldRow.image_preview_url ? oldRow.image_preview_url : URL.createObjectURL(imageFile)) : ''};
    if(idx==='') expenses.push(row); else expenses[parseInt(idx,10)]=row;
    renderExpenses(); expenseModal.hide();
});
$(document).on('click','.edit-expense',function(){ openExpenseEdit(parseInt($(this).data('index'),10)); });
$(document).on('click','.delete-expense',function(){
    const idx=parseInt($(this).data('index'),10);
    Swal.fire({title:'Delete this cost?',text:'This row will be removed from the activity.',icon:'warning',showCancelButton:true,confirmButtonText:'Delete'}).then(r=>{ if(r.isConfirmed){ expenses.splice(idx,1); renderExpenses(); } });
});

$('#btnSave').on('click',function(){
    if(!$('#organization_id').val() || !$('#department').val()){ Swal.fire('Error','Organization and Department are required','error'); return; }
    const fd=new FormData();
    if(CAN_MANAGE_ENTRY){
        fd.append('staff_id',$('#staff_id').val()||'');
        fd.append('activity_at',$('#activity_at').val()||'');
    }
    fd.append('organization_id',$('#organization_id').val());
    fd.append('department_id',$('#department').val());
    fd.append('department',$('#department option:selected').data('title')||$('#department option:selected').text());
    fd.append('contact_id',$('#contact_id').val()||'');
    fd.append('contact_person',$('#contact_id option:selected').data('name')||'');
    fd.append('work_details',$('#work_details').val());
    fd.append('remarks',$('#remarks').val());
    @if($isAdmin) fd.append('status',$('#status').val()); @endif

    travels.forEach((r,i)=>{
        fd.append(`travels[${i}][from_location]`,r.from_location||'');
        fd.append(`travels[${i}][to_location]`,r.to_location||'');
        fd.append(`travels[${i}][vehicle]`,r.vehicle||'');
        fd.append(`travels[${i}][distance]`,r.distance||0);
        fd.append(`travels[${i}][cost]`,r.cost||0);
        fd.append(`travels[${i}][existing_image_url]`,r.image_url||'');
        if(r.image_file) fd.append(`travels[${i}][image]`,r.image_file);
    });
    expenses.forEach((r,i)=>{
        fd.append(`expenses[${i}][expense_type_id]`,r.expense_type_id||'');
        fd.append(`expenses[${i}][amount]`,r.amount||0);
        fd.append(`expenses[${i}][note]`,r.note||'');
        fd.append(`expenses[${i}][existing_image_url]`,r.image_url||'');
        if(r.image_file) fd.append(`expenses[${i}][image]`,r.image_file);
    });

    const btn=$(this), oldText=btn.text();
    btn.prop('disabled',true).text('Saving...');
    $.ajax({
        url:ACTIVITY_URL,type:'POST',data:fd,processData:false,contentType:false,
        success:r=>{
            btn.prop('disabled',false).text(oldText);
            Swal.fire('Success',r.message,'success').then(()=>{ if(!@json($editing)) window.location.href=@json(route('activities.index')); });
        },
        error:x=>{
            btn.prop('disabled',false).text(oldText);
            let m=x.responseJSON?.message||'Something went wrong';
            if(x.status===422&&x.responseJSON?.errors) m=Object.values(x.responseJSON.errors)[0][0];
            Swal.fire('Error',m,'error');
        }
    });
});
</script>
@endpush
