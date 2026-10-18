@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Create Sale
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Create Sale</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item">Create</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
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
        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
                    @csrf

                    <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                    <input type="hidden" name="quotation_id" value="{{ $quotation?->id }}">
                    <input type="hidden" name="organization_contact_id" value="{{ $lead?->organization_contact_id ?? $quotation?->organization_contact_id }}">

                    <div class="row g-3">
                        @include('backend.partials.sale-organization')
                        <div class="col-md-3">
                            <label class="form-label">Client Name</label>
                            <input class="form-control" name="client_name" value="{{ $lead?->person_name ?? $quotation?->client_name }}" placeholder="Client name">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Client Phone</label>
                            <input class="form-control" name="client_phone" value="{{ $lead?->person_phone ?? $quotation?->client_phone }}" placeholder="Phone">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Client Email</label>
                            <input class="form-control" name="client_email" value="{{ $lead?->person_email ?? $quotation?->client_email }}" placeholder="Email">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Sale Date</label>
                            <input type="date" class="form-control" name="sale_date" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Sales Status</label>
                            <select class="form-control" name="status_stage_id">
                                <option value="">--</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" class="form-control" name="discount_amount" id="discount_amount"
                                   value="{{ $quotation?->discount_amount ?? 0 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label d-block">VAT / Tax</label>
                            <div class="form-check mt-2">
                                <input type="hidden" name="tax_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="tax_enabled" id="tax_enabled" value="1" {{ $quotation?->tax_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_enabled">Include VAT / Tax ({{ number_format($taxRate,2) }}%)</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Items</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddRow">
                            <i class="feather-plus"></i> Add Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="240">Product/Custom Item</th>
                                    <th>Description (Optional)</th>
                                    <th width="90">Qty <span class="required-indicator">*</span></th>
                                    <th width="90">Unit</th>
                                    <th width="120">Unit Price <span class="required-indicator">*</span></th>
                                    <th width="90">Tax %</th>
                                    <th width="130">Amount</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <b id="subTotalText">0.00</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <b id="taxText">0.00</b>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Total</span>
                                    <b id="grandTotalText">0.00</b>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('sales.index') }}" class="btn btn-light-brand">Cancel</a>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@include('backend.partials.sale-items-script')
@endpush