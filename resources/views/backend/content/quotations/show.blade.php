@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Quotation {{ $q->quotation_no }}
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Quotation</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">Quotations</a></li>
                <li class="breadcrumb-item">{{ $q->quotation_no }}</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route('quotations.edit',$q->id) }}" class="btn btn-primary"><i class="feather-edit me-1"></i> Edit</a>
            <a href="{{ route('quotations.pdf',$q->id) }}" class="btn btn-dark"><i class="feather-download me-1"></i> PDF</a>
            <a href="{{ route('quotations.index') }}" class="btn btn-light-brand">Back</a>
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
                            <div class="text-end"><small class="text-muted text-uppercase">Quotation</small><div class="fw-bold" style="color:#17324d">{{ $q->quotation_no }}</div></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ $q->quotation_no }}</h5>
                                <div class="text-muted">Issue: {{ optional($q->issue_date)->format('d M Y') }} | Valid: {{ optional($q->valid_until)->format('d M Y') }}</div>
                            </div>
                            <div>
                                @if($q->statusStage)
                                    <span class="badge bg-light text-dark" style="border:1px solid #eee;">
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $q->statusStage->color }};margin-right:6px;"></span>
                                        {{ $q->statusStage->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Client</h6>
                                <div><b>{{ $q->client_name }}</b></div>
                                <div>{{ $q->client_phone }}</div>
                                <div>{{ $q->client_email }}</div>
                                <div class="text-muted">{{ $q->client_address }}</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6>Summary</h6>
                                <div>Sub Total: <b>{{ number_format((float)$q->sub_total,2) }}</b></div>
                                <div>Discount: <b>{{ number_format((float)$q->discount_amount,2) }}</b></div>
                                <div>Tax: <b>{{ number_format((float)$q->tax_amount,2) }}</b></div>
                                <div class="mt-2">Total: <b>{{ number_format((float)$q->grand_total,2) }} {{ $q->currency }}</b></div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-2">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th width="90">Qty</th>
                                    <th width="120">Unit Price</th>
                                    <th width="80">Tax %</th>
                                    <th width="130">Line Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($q->items as $it)
                                    <tr>
                                        <td>
                                            <b>{{ $it->item_name }}</b>
                                            @if($it->description)
                                                <div class="text-muted">{{ $it->description }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $it->qty }} {{ $it->unit }}</td>
                                        <td>{{ number_format((float)$it->unit_price,2) }}</td>
                                        <td>{{ number_format((float)$it->tax_rate,2) }}</td>
                                        <td>{{ number_format((float)$it->line_total,2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($q->description)
                            <hr>
                            <h6>Description</h6>
                            <div class="text-muted">{!! nl2br(e($q->description)) !!}</div>
                        @endif

                        @if($q->note_for_recipient)
                            <hr>
                            <h6>Note for recipient</h6>
                            <div class="text-muted">{!! nl2br(e($q->note_for_recipient)) !!}</div>
                        @endif

                        @if($q->terms)
                            <hr>
                            <h6>Terms & Conditions</h6>
                            <div class="text-muted">{!! nl2br(e($q->terms)) !!}</div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Mail Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2">Send to Client</h6>

                        <form method="POST" action="{{ route('quotations.mail',$q->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">To</label>
                                <input type="email" name="to" class="form-control" value="{{ $q->client_email }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" value="Quotation - {{ $q->quotation_no }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="4">Assalamu Alaikum,
Please find the attached quotation ({{ $q->quotation_no }}).
Thanks.</textarea>
                            </div>

                            <button class="btn btn-primary w-100">
                                <i class="feather-send me-1"></i> Send Mail (PDF Attached)
                            </button>
                        </form>

                        <hr>

                        <a href="{{ route('quotations.pdf',$q->id) }}" class="btn btn-dark w-100">
                            <i class="feather-download me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection