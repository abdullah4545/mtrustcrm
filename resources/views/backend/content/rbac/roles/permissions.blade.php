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

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="input-group">
                                <span class="input-group-text"><i class="feather-search"></i></span>
                                <input type="text" id="permissionSearch" class="form-control" placeholder="Search permission or module...">
                            </div>
                        </div>
                        @foreach($grouped as $module => $perms)
                            <div class="col-md-6 col-xl-4 permission-module" data-module-name="{{ strtolower($module) }}">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <b class="text-uppercase">{{ $module }}</b>
                                        <div class="btn-group btn-group-sm"><button type="button" class="btn btn-light select-all" data-module="{{ $module }}">All</button><button type="button" class="btn btn-outline-secondary clear-all" data-module="{{ $module }}">None</button></div>
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
                                                <span class="permission-name">{{ $p->name }}</span>
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
document.querySelectorAll('.clear-all').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.module-'+btn.dataset.module).forEach(ch=>ch.checked=false)}));
document.getElementById('permissionSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase().trim();document.querySelectorAll('.permission-module').forEach(card=>{card.style.display=(!q||card.innerText.toLowerCase().includes(q))?'':'none';});});
</script>
@endpush