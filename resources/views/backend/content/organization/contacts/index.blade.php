@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Organization Contacts
@endsection

@section('maincontent')
<div class="nxl-content">

    {{-- ================= BREADCRUMB ================= --}}
    <div class="page-header d-flex" style="justify-content: space-between;">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Organization Contacts</h5>
            </div>

            <div class="d-none d-lg-block">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('organization/manage') }}">Organizations</a></li>
                    <li class="breadcrumb-item">Contacts</li>
                </ul>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center">

            {{-- ADD BUTTON --}}
            <button type="button" class="btn btn-primary" id="btnOpenCreate">
                <i class="feather-plus"></i>
                <span class="d-none d-lg-block">&nbsp;&nbsp;Add Contact</span>
            </button>

        </div>
    </div>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="main-content">

        <div class="row">

            {{-- ================= LEFT: ORGANIZATION INFO ================= --}}
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">

                        <h6 class="fw-bold mb-2">{{ $organization->name }}</h6>

                        <div class="fs-12 text-muted">
                            <div><b>Address:</b> {{ $organization->address ?? '-' }}</div>
                            <div><b>Phone:</b> {{ $organization->phone_primary ?? '-' }}</div>
                            <div><b>Email:</b> {{ $organization->email ?? '-' }}</div>
                            <div><b>Status:</b> {{ $organization->status ?? '-' }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ================= RIGHT: TABLE ================= --}}
            <div class="col-xl-8">

                <div class="card">
                    <div class="card-body p-2 p-lg-4">

                        <div class="table-responsive">
                            <table class="table table-bordered" id="contactTable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>K.O.L</th>
                                    <th>Status</th>
                                    <th width="120">Action</th>
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

        </div>
    </div>
</div>
@endsection


{{-- ================= MODAL ================= --}}
@push('modals')
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="contactForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="contact_id">

                    <div class="row g-2">

                        <div class="col-md-4">
                            <label class="form-label">Title</label>
                            <input type="text" id="title" class="form-control" placeholder="Dr / Mr / Ms">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Name</label>
                            <input type="text" id="name" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" id="phone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Two</label>
                            <input type="text" id="phone_two" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="address" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" id="image" class="form-control" accept="image/*">
                            <div id="imgPreview" class="mt-2"></div>
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_primary">
                                <label class="form-check-label">K.O.L</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Additional Info</label>
                            <textarea id="additional_info" class="form-control" rows="3"></textarea>
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


{{-- ================= SCRIPTS ================= --}}
@push('scripts')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
@media (max-width:768px){
    #contactTable_wrapper,
    #contactTable{
        display:none !important;
    }
    #mobileCards{
        display:block !important;
    }
}
@media (min-width:769px){
    #mobileCards{
        display:none !important;
    }
}
</style>

<script>
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

const ROUTE_DATATABLE = "{{ route('org.contacts.datatable', $organization->id) }}";
const ROUTE_STORE     = "{{ route('org.contacts.store', $organization->id) }}";
const ROUTE_SHOW      = "{{ url('organization/contacts') }}";
const ROUTE_UPDATE    = "{{ url('organization/contacts') }}";
const ROUTE_DELETE    = "{{ url('organization/contacts') }}";

let table, modal;

$(document).ready(function(){

    modal = new bootstrap.Modal(document.getElementById('contactModal'));

    table = $('#contactTable').DataTable({
        processing:true,
        serverSide:true,
        ajax: ROUTE_DATATABLE,
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'image', orderable:false, searchable:false},
            {data:'title'},
            {data:'name'},
            {data:'phone'},
            {data:'email'},
            {data:'is_primary', orderable:false, searchable:false},
            {data:'status', orderable:false, searchable:false},
            {data:'action', orderable:false, searchable:false},
        ],
        drawCallback:function(settings){
            renderMobileCards(this.api().rows().data().toArray());
        }
    });

    function renderMobileCards(data){
        let html = '';

        data.forEach(item=>{
            html += `
            <div class="card mb-2">
                <div class="card-body p-2">

                    <h6>${item.name}</h6>
                    <div class="small text-muted">${item.title ?? ''}</div>

                    <div class="small"><b>Phone:</b> ${item.phone}</div>
                    <div class="small"><b>Email:</b> ${item.email}</div>

                    <div class="mt-2 d-flex">${item.action}</div>

                </div>
            </div>`;
        });

        $('#mobileCards').html(html);
    }

    // open create
    $('#btnOpenCreate').on('click', function(){
        clearForm();
        $('#modalTitle').text('Add Contact');
        modal.show();
    });

    // save
    $('#btnSave').on('click', function(e){
        e.preventDefault();

        const id = $('#contact_id').val();
        let fd = new FormData();

        fd.append('title', $('#title').val());
        fd.append('name', $('#name').val());
        fd.append('email', $('#email').val());
        fd.append('phone', $('#phone').val());
        fd.append('phone_two', $('#phone_two').val());
        fd.append('address', $('#address').val());
        fd.append('additional_info', $('#additional_info').val());
        fd.append('status', $('#status').val());
        fd.append('is_primary', $('#is_primary').is(':checked') ? 1 : 0);

        if($('#image')[0].files[0]){
            fd.append('image', $('#image')[0].files[0]);
        }

        // create
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

        // update
        fd.append('_method','POST');

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

    // edit
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.get(ROUTE_SHOW+'/'+id, function(res){
            clearForm();
            const d = res.data;

            $('#modalTitle').text('Edit Contact');
            $('#contact_id').val(d.id);

            $('#title').val(d.title);
            $('#name').val(d.name);
            $('#email').val(d.email);
            $('#phone').val(d.phone);
            $('#phone_two').val(d.phone_two);
            $('#address').val(d.address);
            $('#additional_info').val(d.additional_info);
            $('#status').val(d.status);
            $('#is_primary').prop('checked', !!d.is_primary);

            if(d.image_url){
                $('#imgPreview').html(`<img src="{{ asset('') }}${d.image_url}" style="height:60px;border-radius:8px;">`);
            }

            modal.show();
        });
    });

    // delete
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');

        Swal.fire({
            title:'Are you sure?',
            text:'This contact will be deleted!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Yes, delete it!'
        }).then((r)=>{
            if(!r.isConfirmed) return;

            $.post(ROUTE_DELETE+'/'+id, {}, function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            }).fail(xhr => showAjaxError(xhr));
        });
    });


});

function clearForm(){
    $('#contact_id').val('');
    document.getElementById('contactForm').reset();
    $('#imgPreview').html('');
    $('#is_primary').prop('checked', false);
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