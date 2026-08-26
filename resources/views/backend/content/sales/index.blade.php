@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Sales
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Sales</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item">Sales</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i> Create Sale
            </a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters (ONE LINE) --}}
    <div class="main-content mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label mb-1">From</label>
                        <input type="date" id="f_date_from" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">To</label>
                        <input type="date" id="f_date_to" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Sales Status</label>
                        <select id="f_status_stage_id" class="form-control">
                            <option value="">All</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Payment</label>
                        <select id="f_payment_status" class="form-control">
                            <option value="">All</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1">Search</label>
                        <input type="text" id="f_search_text" class="form-control" placeholder="invoice / name / phone / sale no">
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-secondary btn-sm w-100" id="btnReset">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="salesTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Sale Date</th>
                            <th>Client</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Due</th>
                            <th width="180">Action</th>
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

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const ROUTE_DATATABLE = "{{ route('sales.datatable') }}";
const ROUTE_DELETE    = "{{ url('sales') }}";

let table;

$(document).ready(function(){
    table = $('#salesTable').DataTable({
        processing:true, serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){
                d.status_stage_id = $('#f_status_stage_id').val();
                d.payment_status = $('#f_payment_status').val();
                d.search_text = $('#f_search_text').val();
                d.date_from = $('#f_date_from').val();
                d.date_to = $('#f_date_to').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'invoice_no', name:'invoice_no'},
            {data:'sale_date', name:'sale_date'},
            {data:'client_name', name:'client_name'},
            {data:'client_phone', name:'client_phone'},
            {data:'status_badge', orderable:false, searchable:false},
            {data:'pay_badge', orderable:false, searchable:false},
            {data:'grand_total', name:'grand_total'},
            {data:'due_total', name:'due_total'},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#f_status_stage_id,#f_payment_status,#f_date_from,#f_date_to').on('change', ()=>table.ajax.reload());
    $('#f_search_text').on('keyup', ()=>table.ajax.reload());

    $('#btnReset').on('click', function(){
        $('#f_status_stage_id,#f_payment_status,#f_search_text,#f_date_from,#f_date_to').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');
        Swal.fire({title:'Delete?', text:'This sale will be deleted!', icon:'warning', showCancelButton:true}).then((r)=>{
            if(!r.isConfirmed) return;
            $.post(ROUTE_DELETE+'/'+id+'/delete', {}, function(res){
                Swal.fire('Deleted', res.message ?? 'Deleted', 'success');
                table.ajax.reload(null,false);
            }).fail(xhr => showAjaxError(xhr));
        });
    });
});

function showAjaxError(xhr){
    let msg = 'Something went wrong';
    if(xhr.status === 422 && xhr.responseJSON){
        if(xhr.responseJSON.errors){
            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
            msg = xhr.responseJSON.errors[firstKey][0];
        } else if(xhr.responseJSON.message){
            msg = xhr.responseJSON.message;
        }
    } else if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
    }
    Swal.fire('Error', msg, 'error');
}
</script>
@endpush