@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Role Permissions
@endsection

@section('maincontent')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Permissions</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item">{{ $role->name }}</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('roles.permissions.update', $role->id) }}">
                    @csrf

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Role: <b>{{ $role->name }}</b></h5>
                        <button class="btn btn-primary">Save Permissions</button>
                    </div>

                    <div class="row">
                        @foreach($grouped as $module => $perms)
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <b class="text-uppercase">{{ $module }}</b>
                                        <button type="button" class="btn btn-sm btn-light select-all" data-module="{{ $module }}">Select All</button>
                                    </div>

                                    @foreach($perms as $p)
                                        <div class="form-check">
                                            <input class="form-check-input perm-check module-{{ $module }}"
                                                   type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $p->name }}"
                                                   id="perm_{{ md5($p->name) }}"
                                                   {{ in_array($p->name, $assigned) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ md5($p->name) }}">
                                                {{ $p->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.select-all').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const module = btn.dataset.module;
        document.querySelectorAll('.module-'+module).forEach(ch=> ch.checked = true);
    });
});
</script>
@endpush