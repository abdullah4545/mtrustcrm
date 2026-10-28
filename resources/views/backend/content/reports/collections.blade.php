@extends('backend.master')
@section('title','Collection Report')
@section('maincontent')
<style>
.report-table-wrapper,.tour-table-wrap,.table-responsive{max-width:100%;overflow-x:auto}
#reportTable,.tour-report-table{width:100%;table-layout:fixed;min-width:1100px}
#reportTable th,#reportTable td,.tour-report-table th,.tour-report-table td,.table-responsive th,.table-responsive td{white-space:normal;overflow-wrap:anywhere;word-wrap:break-word;vertical-align:top}
#reportTable td div,.tour-report-table td div{max-width:100%;white-space:normal!important;overflow-wrap:anywhere}
.table-responsive table{width:100%;table-layout:fixed;min-width:1000px}
</style>

<div class="nxl-content"><div class="page-header"><div class="page-header-title"><h5>Collection Report</h5></div></div></div>
<div class="main-content">
<form class="card card-body mb-3" method="GET"><div class="row g-2"><div class="col-md-4"><label>From</label><input type="date" name="from" value="{{ $from }}" class="form-control"></div><div class="col-md-4"><label>To</label><input type="date" name="to" value="{{ $to }}" class="form-control"></div>@can('staff.filter')<div class="col-md-3"><label>Received By</label><select name="user_id" class="form-control"><option value="">All Staff</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}" @selected((string)request('user_id')===(string)$staff->id)>{{ $staff->name }}</option>@endforeach</select></div>@endcan<div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Apply</button></div></div></form>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="card p-3"><small>Payments Received</small><h4>{{ $summary['count'] }}</h4></div></div><div class="col-md-6"><div class="card p-3"><small>Total Collection</small><h4 class="text-success">৳{{ number_format($summary['amount'],2) }}</h4></div></div></div>
<div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Date</th><th>Invoice</th><th>Received By</th><th>Client</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead><tbody>@forelse($rows as $r)<tr><td>{{ optional($r->payment_date)->format('d M Y') }}</td><td>{{ $r->sale->invoice_no ?? '-' }}</td><td>{{ $r->receiver->name ?? '-' }}</td><td>{{ $r->sale->client_name ?? '-' }}</td><td>{{ ucfirst($r->method) }}</td><td>{{ $r->transaction_ref ?: '-' }}</td><td class="text-success">৳{{ number_format($r->amount,2) }}</td></tr>@empty<tr><td colspan="7" class="text-center">No data</td></tr>@endforelse</tbody></table>{{ $rows->links() }}</div></div>
</div>
@endsection
