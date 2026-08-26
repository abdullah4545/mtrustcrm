@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Organizations
@endsection

@section('maincontent')
<div class="nxl-content">

    {{-- ================= BREADCRUMB (SEPARATE) ================= --}}
    <div class="page-header d-flex" style="justify-content: space-between;">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Organization</h5>
            </div>
            <div class=" d-none d-lg-block">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Manage</li>
            </ul>
            </div>
        </div>  
        <div class="d-flex gap-2 align-items-center"> 
            {{-- FILTER BUTTON --}}
            <button class="btn btn-outline-secondary"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#filterSidebar">
                <i class="feather-filter"></i> <span class="d-none d-lg-block">Filter</span>
            </button> 
            <a href="{{route('org.quick.create')}}" class="btn btn-primary" >
                <i class="feather-plus"></i><span class="d-none d-lg-block">&nbsp;&nbsp;Add Organization</span>
            </a> 
        </div>
    </div> 
</div>

<div class="main-content"> 

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="card">
        <div class="card-body p-2 p-lg-4">

            <div class="table-responsive">
                <table class="table table-bordered" id="orgTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th> 
                        <th>Geo</th>
                        <th>Status</th>
                        <th width="140">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            {{-- ================= MOBILE CARDS ================= --}}
            <div class="d-block d-md-none mt-3" id="mobileCards"></div>

        </div>
    </div>
</div>
{{-- ================= FILTER SIDEBAR (OFFCANVAS) ================= --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterSidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">

        <div class="row g-2">

            <div class="col-12">
                <select id="f_category" class="form-control">
                    <option value="">All Category</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <select id="f_type" class="form-control">
                    <option value="">All Type</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <select id="f_division" class="form-control">
                    <option value="">All Division</option>
                    @foreach($divisions as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <select id="f_district" class="form-control">
                    <option value="">All District</option>
                </select>
            </div>

            <div class="col-12">
                <select id="f_upazila" class="form-control">
                    <option value="">All Upazila</option>
                </select>
            </div>

            <div class="col-12">
                <select id="f_union" class="form-control">
                    <option value="">All Union</option>
                </select>
            </div>

            <div class="col-12">
                <input type="text" id="f_name" class="form-control" placeholder="Search name...">
            </div>

            <div class="col-12">
                <input type="text" id="f_address" class="form-control" placeholder="Search address...">
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-light-brand w-100" id="btnReset" type="button">
                    Reset
                </button>
            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

{{-- ⚠️ YOUR JS COMPLETELY SAME (UNCHANGED) --}}
{{-- শুধু responsive UI যোগ করা হয়েছে --}}

<style>
@media (max-width: 768px) {
    .table-responsive {
        font-size: 12px;
    }

    .page-header-title h5 {
        font-size: 16px;
    }

    .form-control {
        font-size: 13px;
    }

    #filterBox {
        background: #fff;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
}

@media (max-width: 768px) {

    /* ❌ table completely hide in mobile */
    #orgTable_wrapper,
    #orgTable {
        display: none !important;
    }

    /* ✅ mobile card show */
    #mobileCards {
        display: block !important;
    }
    .offcanvas-end {
        width:calc(100% - 20%) !important; 
    }
}

/* Desktop */
@media (min-width: 769px) {
    #mobileCards {
        display: none !important;
    }
}
 
</style>
 
{{-- আমি একটাও function remove করি নাই --}}

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ✅ CSRF
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

const ROUTE_DATATABLE = "{{ route('org.manage.datatable') }}";
const ROUTE_STORE     = "{{ route('org.manage.store') }}";
const ROUTE_SHOW      = "{{ url('organization/manage') }}";
const ROUTE_UPDATE    = "{{ url('organization/manage') }}";
const ROUTE_DELETE    = "{{ url('organization/manage') }}";

const ROUTE_DISTRICTS = "{{ route('org.geo.districts') }}";
const ROUTE_UPAZILAS  = "{{ route('org.geo.upazilas') }}";
const ROUTE_UNIONS    = "{{ route('org.geo.unions') }}";

let table, modal;

$(document).ready(function(){

  

    // ✅ DataTable
    table = $('#orgTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){
                d.organization_category_id = $('#f_category').val();
                d.organization_type_id     = $('#f_type').val();
                d.name                     = $('#f_name').val();
                d.address                  = $('#f_address').val();
                d.division_id              = $('#f_division').val();
                d.district_id              = $('#f_district').val();
                d.upazila_id               = $('#f_upazila').val();
                d.union_id                 = $('#f_union').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'name', name:'name'},
            {data:'category', orderable:false, searchable:false},
            {data:'type', orderable:false, searchable:false}, 
            {data:'geo', orderable:false, searchable:false},
            {data:'status', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ],
        drawCallback: function(settings){
            let api = this.api();
            let data = api.rows().data().toArray();
            renderMobileCards(data);
        }
    });

    function renderMobileCards(data){
        let html = '';

        data.forEach((item, index) => {
            html += `
            <div class="card mb-2 shadow-sm">
                <div class="card-body p-2 pb-4">

                    <h6 class="mb-1">${item.name}</h6>

                    <div class="small text-muted">
                        ${item.category ?? ''} | ${item.type ?? ''}
                    </div>

                    <div class="small">
                        <b>Address:</b> ${item.address ?? '-'}
                    </div>

                    <div class="small">
                        <b>Geo:</b> ${item.geo ?? '-'}
                    </div>

                    <div class="small">
                        <b>Status:</b> ${item.status ?? ''}
                    </div>

                    <div class="mt-2">
                        ${item.action}
                    </div>

                </div>
            </div>`;
        });

        $('#mobileCards').html(html);
    }

    // ✅ Auto Reload on dropdown change
    $('#f_category, #f_type, #f_division, #f_district, #f_upazila, #f_union').on('change', function () {
        table.ajax.reload();
    });

    // ✅ Auto Reload on typing (debounce)
    let typingTimer;
    $('#f_name, #f_address').on('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function () {
            table.ajax.reload();
        }, 400);
    });

    // ✅ Reset filters
    $('#btnReset').on('click', function(){
        $('#f_category,#f_type,#f_name,#f_address,#f_division,#f_district,#f_upazila,#f_union').val('');
        $('#f_district').html('<option value="">All District</option>');
        $('#f_upazila').html('<option value="">All Upazila</option>');
        $('#f_union').html('<option value="">All Union</option>');
        table.ajax.reload();
    });

    // ✅ open create modal
    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Organization');
        modal.show();
    });

    // ✅ save (create/update)
    $('#btnSave').on('click', function(e){
        e.preventDefault();

        const id = $('#org_id').val();
        let payload = getPayload();

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

   
    // ✅ delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This will be deleted!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Yes, delete it!'
        }).then((r)=>{
            if(!r.isConfirmed) return;

            $.post(ROUTE_DELETE+'/'+id, { _method:'DELETE' })
                .done(res => { Swal.fire('Deleted', res.message ?? 'Deleted', 'success'); table.ajax.reload(null,false); })
                .fail(xhr => showAjaxError(xhr));
        });
    });

    // ✅ FILTER geo chain (division change triggers reload after load)
    $('#f_division').on('change', function(){
        $('#f_district').html('<option value="">All District</option>');
        $('#f_upazila').html('<option value="">All Upazila</option>');
        $('#f_union').html('<option value="">All Union</option>');
        loadDistricts('#f_division', '#f_district').then(()=> table.ajax.reload());
    });

    $('#f_district').on('change', function(){
        $('#f_upazila').html('<option value="">All Upazila</option>');
        $('#f_union').html('<option value="">All Union</option>');
        loadUpazilas('#f_district', '#f_upazila').then(()=> table.ajax.reload());
    });

    $('#f_upazila').on('change', function(){
        $('#f_union').html('<option value="">All Union</option>');
        loadUnions('#f_upazila', '#f_union').then(()=> table.ajax.reload());
    });

    // ✅ MODAL geo chain
    $('#division_id').on('change', function(){
        $('#district_id').html('<option value="">Select</option>');
        $('#upazila_id').html('<option value="">Select</option>');
        $('#union_id').html('<option value="">Select</option>');
        loadDistricts('#division_id', '#district_id');
    });

    $('#district_id').on('change', function(){
        $('#upazila_id').html('<option value="">Select</option>');
        $('#union_id').html('<option value="">Select</option>');
        loadUpazilas('#district_id', '#upazila_id');
    });

    $('#upazila_id').on('change', function(){
        $('#union_id').html('<option value="">Select</option>');
        loadUnions('#upazila_id', '#union_id');
    });

});

function getPayload(){
    return {
        organization_category_id: $('#organization_category_id').val(),
        organization_type_id: $('#organization_type_id').val(),
        name: $('#name').val(),
        address: $('#address').val(),
        division_id: $('#division_id').val(),
        district_id: $('#district_id').val(),
        upazila_id: $('#upazila_id').val(),
        union_id: $('#union_id').val(),
        phone_primary: $('#phone_primary').val(),
        phone_secondary: $('#phone_secondary').val(),
        email: $('#email').val(),
        website: $('#website').val(),
        latitude: $('#latitude').val(),
        longitude: $('#longitude').val(),
        notes: $('#notes').val(),
        status: $('#status').val(),
    };
}

function clearForm(){
    $('#org_id').val('');
    document.getElementById('orgForm').reset();
    $('#district_id,#upazila_id,#union_id').html('<option value="">Select</option>');
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

// ✅ Geo loaders
function loadDistricts(divisionSel, districtSel, selectedId=null){
    const division_id = $(divisionSel).val();
    if(!division_id) return Promise.resolve();

    return $.get(ROUTE_DISTRICTS, {division_id})
        .then(res=>{
            let first = $(districtSel).find('option:first').length ? $(districtSel).find('option:first')[0].outerHTML : '<option value="">Select</option>';
            let html = first;
            res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
            $(districtSel).html(html);
            if(selectedId) $(districtSel).val(selectedId);
        });
}

function loadUpazilas(districtSel, upazilaSel, selectedId=null){
    const district_id = $(districtSel).val();
    if(!district_id) return Promise.resolve();

    return $.get(ROUTE_UPAZILAS, {district_id})
        .then(res=>{
            let first = $(upazilaSel).find('option:first').length ? $(upazilaSel).find('option:first')[0].outerHTML : '<option value="">Select</option>';
            let html = first;
            res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
            $(upazilaSel).html(html);
            if(selectedId) $(upazilaSel).val(selectedId);
        });
}

function loadUnions(upazilaSel, unionSel, selectedId=null){
    const upazila_id = $(upazilaSel).val();
    if(!upazila_id) return Promise.resolve();

    return $.get(ROUTE_UNIONS, {upazila_id})
        .then(res=>{
            let first = $(unionSel).find('option:first').length ? $(unionSel).find('option:first')[0].outerHTML : '<option value="">Select</option>';
            let html = first;
            res.data.forEach(r=> html += `<option value="${r.id}">${r.name}</option>`);
            $(unionSel).html(html);
            if(selectedId) $(unionSel).val(selectedId);
        });
}
</script>

@endpush