@extends('backend.master')

@section('title')
{{ ($business?->business_name ?? 'Medi Trust Solution') }} - Activities
@endsection

@section('maincontent') 

    <div class="nxl-content"> 
        {{-- HEADER --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Activity</h5>
                </div>

                <div class="d-none d-lg-block">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item">Activity</li>
                    </ul>
                </div>
            </div>

            <a href="{{route('activities.quick.create')}}" class="btn btn-primary">
                <i class="feather-plus"></i> Add Activity
            </a>
        </div>

    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <table class="table table-bordered" id="activityTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Organization</th>
                        <th>From</th>
                        <th>To</th>
                        <th>TA</th>
                        <th>DA</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>

@endsection


@push('modals')
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle">Add Activity</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="activityForm" method="POST">
                    @csrf
                    <input type="hidden" id="activity_id">

                    <div class="row g-2">

                        <div class="col-md-3">
                            <label>Date</label>
                            <input type="date" id="date" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Organization</label>
                            <select id="organization_id" class="form-control" required>
                                <option value="">Select Organization</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Department</label>
                            <select id="department" class="form-control" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Contact Person</label>
                            <input type="text" id="contact_person" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>From</label>
                            <input type="text" id="from_location" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>To</label>
                            <input type="text" id="to_location" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Vehicle</label>
                            <input type="text" id="vehicle" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Status</label>
                            <select id="status" class="form-control">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label>Work Details</label>
                            <textarea id="work_details" class="form-control"></textarea>
                        </div>

                        <div class="col-md-3">
                            <label>TA</label>
                            <input type="number" id="ta" class="form-control" value="0">
                        </div>

                        <div class="col-md-3">
                            <label>DA</label>
                            <input type="number" id="da" class="form-control" value="0">
                        </div>

                        <div class="col-md-3">
                            <label>Total</label>
                            <input type="number" id="total" class="form-control" readonly>
                        </div>

                        <div class="col-md-12">
                            <label>Remarks</label>
                            <textarea id="remarks" class="form-control"></textarea>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnSave">Save</button>
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
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table, modal;

    const ROUTE_DATATABLE = "{{ route('activities.datatable') }}";
    const ROUTE_STORE = "{{ route('activities.store') }}";
    const ROUTE_UPDATE = "{{ url('activities') }}";
    const ROUTE_DELETE = "{{ url('activities') }}";
    const ROUTE_ORG = "{{ url('activities/ajax/organizations') }}";
    const ROUTE_DEP = "{{ url('activities/ajax/departments') }}";

    function loadDropdowns(){
        // organization
        $.get(ROUTE_ORG, function(res){
            let html = `<option value="">Select Organization</option>`;
            res.forEach(v=>{
                html += `<option value="${v.id}">${v.name}</option>`;
            });
            $('#organization_id').html(html);
        });

        // department
        $.get(ROUTE_DEP, function(res){
            let html = `<option value="">Select Department</option>`;
            res.forEach(v=>{
                html += `<option value="${v.title}">${v.title}</option>`;
            });
            $('#department').html(html);
        });
    }

    $(document).ready(function(){

   

        table = $('#activityTable').DataTable({
            processing:true,
            serverSide:true,
            ajax: ROUTE_DATATABLE,
            columns:[
                {data:'DT_RowIndex', orderable:false, searchable:false},
                {data:'date'},
                {data:'organization_name'},
                {data:'from_location'},
                {data:'to_location'},
                {data:'ta'},
                {data:'da'},
                {data:'total'},
                {data:'status'},
                {data:'action', orderable:false, searchable:false},
            ]
        });

        

        $('#ta,#da').on('keyup change', function(){
            let ta = parseFloat($('#ta').val()) || 0;
            let da = parseFloat($('#da').val()) || 0;
            $('#total').val(ta + da);
        });
  

        $(document).on('click','.btn-delete',function(){
            let id = $(this).data('id');

            if(confirm('Delete?')){
                $.post(ROUTE_DELETE+'/'+id+'/delete',{},function(){
                    table.ajax.reload();
                });
            }
        });

    });

    function clearForm(){
        $('#activityForm')[0].reset();
        $('#activity_id').val('');
        $('#total').val(0);
    }
</script>
@endpush