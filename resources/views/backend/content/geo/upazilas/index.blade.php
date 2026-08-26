@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Geo Upazilas
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Geo</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Upazilas</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="btnOpenCreate">
                    <i class="feather-plus me-2"></i> Add Upazila
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                {{-- ✅ Filters --}}
                <div class="row mb-3">
                    <div class="col-xl-4">
                        <label class="form-label">Filter by Division</label>
                        <select class="form-control" id="filterDivision">
                            <option value="">-- All Divisions --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-4">
                        <label class="form-label">Filter by District</label>
                        <select class="form-control" id="filterDistrict">
                            <option value="">-- All Districts --</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="upazilasTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Name</th>
                            <th>Code</th>
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

{{-- ✅ Modal --}}
@push('modals')
    <div class="modal fade" id="upazilaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Upazila</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="upazilaForm">
                        @csrf
                        <input type="hidden" id="upazila_id" value="">

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Division *</label>
                                    <select class="form-control" id="division_id" required>
                                        <option value="">-- Select Division --</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">District *</label>
                                    <select class="form-control" id="district_id" required>
                                        <option value="">-- Select District --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Upazila Name *</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" id="code">
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
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
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.modal-backdrop{ z-index:1040 !important; }
.modal{ z-index:1055 !important; }
</style>

<script>
const ROUTE_DATATABLE = "{{ route('upazilas.datatable') }}";
const ROUTE_STORE     = "{{ route('upazilas.store') }}";
const ROUTE_SHOW      = "{{ url('geo/upazilas') }}";
const ROUTE_UPDATE    = "{{ url('geo/upazilas') }}";
const ROUTE_DELETE    = "{{ url('geo/upazilas') }}";
const ROUTE_AJAX_DIST = "{{ route('ajax.districts') }}";

let table;
let upazilaModal;

$(document).ready(function () {
    upazilaModal = new bootstrap.Modal(document.getElementById('upazilaModal'));

    table = $('#upazilasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ROUTE_DATATABLE,
            data: function (d) {
                d.division_id = $('#filterDivision').val();
                d.district_id = $('#filterDistrict').val();
            }
        },
        columns: [
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'division', orderable:false},
            {data:'district', orderable:false},
            {data:'name'},
            {data:'code'},
            {data:'is_active', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    // ✅ Filter division → load districts + reload
    $('#filterDivision').on('change', function(){
        loadDistricts($(this).val(), '#filterDistrict', true, null);
        table.ajax.reload();
    });

    $('#filterDistrict').on('change', function(){
        table.ajax.reload();
    });

    // ✅ Modal division change → load districts
    $('#division_id').on('change', function(){
        loadDistricts($(this).val(), '#district_id', false, null);
    });

    // ✅ Open create
    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Upazila');

        // optional auto from filters
        const divId = $('#filterDivision').val();
        const distId = $('#filterDistrict').val();
        if(divId){
            $('#division_id').val(divId);
            loadDistricts(divId, '#district_id', false, distId);
        }

        upazilaModal.show();
    });

    // ✅ Save (create/update)
    $('#btnSave').on('click', function(e){
        e.preventDefault();

        const id = $('#upazila_id').val();

        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            division_id: $('#division_id').val(),
            district_id: $('#district_id').val(),
            name: $('#name').val(),
            code: $('#code').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };

        if(!id){
            $.post(ROUTE_STORE, payload)
                .done(function(res){
                    Swal.fire('Success', res.message ?? 'Created', 'success');
                    upazilaModal.hide();
                    table.ajax.reload(null,false);
                })
                .fail(function(xhr){ showAjaxError(xhr); });
            return;
        }

        payload._method = 'PUT';
        $.post(ROUTE_UPDATE+'/'+id, payload)
            .done(function(res){
                Swal.fire('Success', res.message ?? 'Updated', 'success');
                upazilaModal.hide();
                table.ajax.reload(null,false);
            })
            .fail(function(xhr){ showAjaxError(xhr); });
    });

    // ✅ Edit
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            $('#modalTitle').text('Edit Upazila');
            $('#upazila_id').val(res.data.id);

            $('#name').val(res.data.name);
            $('#code').val(res.data.code);
            $('#is_active').prop('checked', !!res.data.is_active);

            // set division then load districts then select district
            $('#division_id').val(res.data.division_id);
            loadDistricts(res.data.division_id, '#district_id', false, res.data.district_id);

            upazilaModal.show();
        });
    });

    // ✅ Delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will be deleted permanently!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(!result.isConfirmed) return;

            $.post(ROUTE_DELETE+'/'+id, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            })
            .done(function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            })
            .fail(function(xhr){ showAjaxError(xhr); });
        });
    });
});

function loadDistricts(divisionId, targetSelect, allOption, selectedId){
    if(!divisionId){
        $(targetSelect).html(allOption
            ? '<option value="">-- All Districts --</option>'
            : '<option value="">-- Select District --</option>');
        return;
    }

    $(targetSelect).html('<option value="">Loading...</option>');

    $.get(ROUTE_AJAX_DIST, {division_id: divisionId}, function(res){
        let html = allOption
            ? '<option value="">-- All Districts --</option>'
            : '<option value="">-- Select District --</option>';

        res.data.forEach(function(d){
            const sel = (selectedId && selectedId == d.id) ? 'selected' : '';
            html += `<option value="${d.id}" ${sel}>${d.name}</option>`;
        });

        $(targetSelect).html(html);
    });
}

function clearForm(){
    $('#upazila_id').val('');
    $('#division_id').val('');
    $('#district_id').html('<option value="">-- Select District --</option>');
    $('#name').val('');
    $('#code').val('');
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
