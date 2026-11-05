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

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
                    @csrf

                    <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                    <input type="hidden" name="quotation_id" value="{{ $quotation?->id }}">
                    <input type="hidden" name="organization_contact_id" value="{{ $lead?->organization_contact_id ?? $quotation?->organization_contact_id }}">

                    @include('backend.partials.sale-direct-form')

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

@endpush