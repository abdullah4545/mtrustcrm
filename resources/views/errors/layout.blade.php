@extends('backend.master')

@php
    $code = $code ?? 500;
    $title = $title ?? 'Something went wrong';
    $message = $message ?? 'An unexpected error occurred. Please try again.';
    $icon = $icon ?? 'feather-alert-triangle';
@endphp

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - {{ $code }} | {{ $title }}
@endsection

@section('maincontent')
<div class="nxl-content">
    <div class="main-content">
        <div class="card">
            <div class="card-body text-center py-5">

                <div class="mb-3">
                    <i class="{{ $icon }}" style="font-size:64px;"></i>
                </div>

                <h1 class="mb-2" style="font-size:48px;font-weight:800;">
                    {{ $code }}
                </h1>

                <h4 class="mb-2">{{ $title }}</h4>

                <p class="text-muted mb-4" style="max-width:720px;margin:0 auto;">
                    {{ $message }}
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ url()->previous() }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-1"></i> Go Back
                    </a>

                    <a href="{{ url('/') }}" class="btn btn-primary">
                        <i class="feather-home me-1"></i> Dashboard
                    </a>

                    @auth
                        <a href="{{ route('logout') }}"
                           class="btn btn-outline-danger"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="feather-log-out me-1"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endauth
                </div>

                @if(config('app.debug'))
                    <div class="mt-4 text-start" style="max-width:900px;margin:0 auto;">
                        <div class="alert alert-warning">
                            <b>Debug Mode:</b> You are seeing this because <code>APP_DEBUG=true</code>.
                            Turn it off in production.
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection