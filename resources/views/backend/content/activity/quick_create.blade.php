@extends('backend.master')
@section('title', ($business?->business_name ?? 'Medi Trust Solution').' - Field Activity Entry')
@section('maincontent')
<div class="nxl-content"><div class="page-header"><ul class="breadcrumb mt-1"><li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('activities.index') }}">Field Activity</a></li><li class="breadcrumb-item active">Activity Entry</li></ul></div><div class="main-content"><div class="mb-3"><h5 class="mb-0">Today's Field Activity</h5></div>@include('backend.content.activity.partials.form')</div></div>
@endsection
