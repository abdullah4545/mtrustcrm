@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Edit Sale
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Edit Sale</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item">{{ $sale->invoice_no }}</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route('sales.show',$sale->id) }}" class="btn btn-secondary"><i class="feather-eye me-1"></i> View</a>
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

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('sales.update',$sale->id) }}">
                    @csrf
                    <input type="hidden" name="organization_contact_id" value="{{ old('organization_contact_id', $sale->organization_contact_id) }}">

                    @include('backend.partials.sale-direct-form')

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Update</button>
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