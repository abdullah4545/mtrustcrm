@extends('backend.master')
@section('title','Sales Report')
@section('maincontent')
<div class="nxl-content"><div class="page-header"><div class="page-header-title"><h5>Sales Report</h5></div></div></div>
<div class="main-content">
<form class="card card-body mb-3" method="GET"><div class="row g-2"><div class="col-md-4"><label>From</label><input type="date" name="from" value="{{ $from }}" class="form-control"></div><div class="col-md-4"><label>To</label><input type="date" name="to" value="{{ $to }}" class="form-control"></div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary w-100">Apply Filter</button></div></div></form>
<div class="row g-3 mb-3"><div class="col-md-3"><div class="card p-3"><small>Sales</small><h4>{{ $summary['sales_count'] }}</h4></div></div><div class="col-md-3"><div class="card p-3"><small>Total</small><h4>৳{{ number_format($summary['sales_total'],2) }}</h4></div></div><div class="col-md-3"><div class="card p-3"><small>Collected</small><h4 class="text-success">৳{{ number_format($summary['paid_total'],2) }}</h4></div></div><div class="col-md-3"><div class="card p-3"><small>Due</small><h4 class="text-danger">৳{{ number_format($summary['due_total'],2) }}</h4></div></div></div>
<div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Date</th><th>Invoice</th><th>Client</th><th>Phone</th><th>Total</th><th>Paid</th><th>Due</th></tr></thead><tbody>@forelse($rows as $r)<tr><td>{{ optional($r->sale_date)->format('d M Y') }}</td><td><a href="{{ route('sales.show',$r->id) }}">{{ $r->invoice_no }}</a></td><td>{{ $r->client_name }}</td><td>{{ $r->client_phone }}</td><td>৳{{ number_format($r->grand_total,2) }}</td><td class="text-success">৳{{ number_format($r->paid_total,2) }}</td><td class="text-danger">৳{{ number_format($r->due_total,2) }}</td></tr>@empty<tr><td colspan="7" class="text-center">No data</td></tr>@endforelse</tbody></table>{{ $rows->links() }}</div></div>
</div>
@endsection
