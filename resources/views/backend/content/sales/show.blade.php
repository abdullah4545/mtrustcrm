@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Invoice {{ $sale->invoice_no }}
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Invoice</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item">{{ $sale->invoice_no }}</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route('sales.edit',$sale->id) }}" class="btn btn-primary"><i class="feather-edit me-1"></i> Edit</a>
            <a href="{{ route('sales.pdf',$sale->id) }}" class="btn btn-dark"><i class="feather-download me-1"></i> PDF</a>
            <a href="{{ route('sales.index') }}" class="btn btn-light-brand">Back</a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="main-content">
        <div class="row g-3">

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4 pb-3 border-bottom">
                            <img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" alt="Medi Trust Solution" style="height:48px;max-width:330px;object-fit:contain;object-position:left center">
                            <div class="text-end"><small class="text-muted text-uppercase">Invoice</small><div class="fw-bold" style="color:#17324d">{{ $sale->invoice_no }}</div></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ $sale->invoice_no }}</h5>
                                <div class="text-muted">Sale Date: {{ optional($sale->sale_date)->format('d M Y') }}</div>
                            </div>
                            <div class="text-end">
                                <div>Grand: <b>{{ number_format((float)$sale->grand_total,2) }}</b></div>
                                <div>Paid: <b>{{ number_format((float)$sale->paid_total,2) }}</b></div>
                                <div>Due: <b>{{ number_format((float)$sale->due_total,2) }}</b></div>
                                <div class="mt-1">
                                    @php
                                        $p = $sale->payment_status;
                                        $cls = $p=='paid'?'bg-success':($p=='partial'?'bg-warning':'bg-danger');
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ strtoupper($p) }}</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Organization</h6>
                                <div><b>{{ $sale->organization?->name ?? 'Unknown / legacy' }}</b></div><div><b>{{ $sale->client_name }}</b></div>
                                <div>{{ $sale->client_phone }}</div>
                                <div>{{ $sale->client_email }}</div>
                                <div class="text-muted">{{ $sale->client_address }}</div>
                            </div>
                            <div class="col-md-6 text-end">
                                @if($sale->statusStage)
                                    <span class="badge bg-light text-dark" style="border:1px solid #eee;">
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $sale->statusStage?->color }};margin-right:6px;"></span>
                                        {{ $sale->statusStage?->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <hr>

                        @if($sale->notes)
                            <hr>
                            <h6>Notes</h6>
                            <div class="text-muted">{!! nl2br(e($sale->notes)) !!}</div>
                        @endif

                    </div>
                </div>

                {{-- Payments list --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-2">Payments</h6>
                        @if($sale->opening_paid_total > 0)<div class="alert alert-light border">Paid amount entered with sale: {{ number_format((float)$sale->opening_paid_total, 2) }}. Subsequent payments appear below.</div>@endif
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>TXN</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($sale->payments as $p)
                                    <tr>
                                        <td>{{ optional($p->payment_date)->format('d M Y') }}</td>
                                        <td>{{ $p->method }}</td>
                                        <td>{{ $p->transaction_ref ?? '-' }}</td>
                                        <td class="text-end">{{ number_format((float)$p->amount,2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No payments yet</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right panel: Add Payment + Send Mail --}}
            <div class="col-md-4">

                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2">Add Payment</h6>
                        <form method="POST" action="{{ route('sales.payment.add',$sale->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" class="form-control" name="amount" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Method</label>
                                <select class="form-control" name="method">
                                    <option value="cash">Cash</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Transaction Ref</label>
                                <input class="form-control" name="transaction_ref">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Note</label>
                                <textarea class="form-control" name="note" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary w-100"><i class="feather-plus me-1"></i> Add Payment</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-2">Send Invoice (PDF)</h6>
                        <form method="POST" action="{{ route('sales.mail',$sale->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">To</label>
                                <input type="email" class="form-control" name="to" value="{{ $sale->client_email }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Subject</label>
                                <input class="form-control" name="subject" value="Invoice - {{ $sale->invoice_no }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" rows="4">Assalamu Alaikum,
Please find the attached invoice ({{ $sale->invoice_no }}).
Thanks.</textarea>
                            </div>
                            <button class="btn btn-dark w-100"><i class="feather-send me-1"></i> Send Mail</button>
                        </form>

                        <hr>
                        <a href="{{ route('sales.pdf',$sale->id) }}" class="btn btn-secondary w-100">
                            <i class="feather-download me-1"></i> Download PDF
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection