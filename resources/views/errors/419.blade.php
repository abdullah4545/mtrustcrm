@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - 419 | Session Expired
@endsection

@section('maincontent')
<div class="nxl-content">
    <div class="main-content">
        <div class="card">
            <div class="card-body text-center py-5">

                <div class="mb-3">
                    <i class="feather-refresh-cw" style="font-size:64px;"></i>
                </div>

                <h1 class="mb-2" style="font-size:48px;font-weight:800;">419</h1>
                <h4 class="mb-2">Session Expired</h4>

                <p class="text-muted mb-4" style="max-width:720px;margin:0 auto;">
                    Your session has expired (CSRF token mismatch). Please reload the page and try again.
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        <i class="feather-refresh-cw me-1"></i> Reload Page
                    </button>

                    <a href="{{ url()->previous() }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-1"></i> Go Back
                    </a>

                    <a href="{{ url('/') }}" class="btn btn-outline-dark">
                        <i class="feather-home me-1"></i> Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection