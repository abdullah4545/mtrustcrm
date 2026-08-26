@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Quotations
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Quotations</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item">Quotations</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i> Create Quotation
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
                    <div class="col-md-3">
                        <label class="form-label mb-1">Status</label>
                        <select id="f_status_stage_id" class="form-control">
                            <option value="">All</option>
                            @php
                                $statuses = \App\Models\StatusStage::where('status',1)->where('is_for','quotation')->orderBy('name')->get();
                            @endphp
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Search</label>
                        <input type="text" id="f_search_text" class="form-control" placeholder="quotation no / name / phone / email">
                    </div>
                    <div class="col-md-2">
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
                    <table class="table table-bordered" id="qtTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Quotation No</th>
                            <th>Organization</th>
                            <th>Client</th>
                            <th>Issue</th>
                            <th>Valid Till</th>
                            <th>Status</th>
                            <th>Total</th>
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

const ROUTE_DATATABLE = "{{ route('quotations.datatable') }}";
const ROUTE_DELETE    = "{{ url('quotations') }}";

let table;

$(document).ready(function(){
    table = $('#qtTable').DataTable({
        processing:true, serverSide:true,
        ajax:{
            url: ROUTE_DATATABLE,
            data: function(d){
                d.status_stage_id = $('#f_status_stage_id').val();
                d.search_text = $('#f_search_text').val();
                d.date_from = $('#f_date_from').val();
                d.date_to = $('#f_date_to').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'quotation_no', name:'quotation_no'},
            {data:'org_name', orderable:false, searchable:false},
            {data:'client_name', name:'client_name'},
            {data:'issue_date', name:'issue_date'},
            {data:'valid_until', name:'valid_until'},
            {data:'status_badge', orderable:false, searchable:false},
            {data:'grand_total', name:'grand_total'},
            {data:'action', orderable:false, searchable:false},
        ]
    });

    $('#f_status_stage_id,#f_date_from,#f_date_to').on('change', ()=>table.ajax.reload());
    $('#f_search_text').on('keyup', ()=>table.ajax.reload());

    $('#btnReset').on('click', function(){
        $('#f_status_stage_id,#f_search_text,#f_date_from,#f_date_to').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');
        Swal.fire({title:'Delete?', text:'This quotation will be deleted!', icon:'warning', showCancelButton:true}).then((r)=>{
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