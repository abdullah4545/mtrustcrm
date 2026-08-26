@extends('backend.master')
@section('title', ($business?->business_name ?? 'Medi Trust Solution').' - DA / Expense Types')
@section('maincontent')
<div class="nxl-content">
    <div class="page-header"><div class="page-header-left"><h5 class="m-b-10">DA / Expense Types</h5><ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li><li class="breadcrumb-item">Settings</li></ul></div></div>
    <div class="main-content">
        @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
        <div class="row g-3">
            <div class="col-lg-4"><div class="card"><div class="card-header"><h5>Add Expense Type</h5></div><div class="card-body">
                <form method="POST" action="{{ route('expense-types.store') }}">@csrf
                    <div class="mb-3"><label class="form-label">Name *</label><input name="name" class="form-control" placeholder="Breakfast / Lunch / Hotel" required></div>
                    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" min="0" name="sort_order" class="form-control" value="0"></div>
                    <div class="form-check mb-3"><input type="hidden" name="status" value="0"><input class="form-check-input" type="checkbox" name="status" value="1" checked id="new_status"><label class="form-check-label" for="new_status">Active</label></div>
                    <button class="btn btn-primary w-100">Save</button>
                </form>
            </div></div></div>
            <div class="col-lg-8"><div class="card"><div class="card-header"><h5>Expense Types</h5></div><div class="card-body table-responsive">
                <table class="table align-middle"><thead><tr><th>Name</th><th>Order</th><th>Status</th><th width="210">Action</th></tr></thead><tbody>
                @forelse($types as $type)
                    <tr><form method="POST" action="{{ route('expense-types.update',$type) }}">@csrf
                        <td><input name="name" class="form-control" value="{{ $type->name }}" required></td>
                        <td><input type="number" min="0" name="sort_order" class="form-control" value="{{ $type->sort_order }}"></td>
                        <td><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" {{ $type->status?'checked':'' }}></td>
                        <td><button class="btn btn-sm btn-primary">Update</button></form>
                            <form method="POST" action="{{ route('expense-types.destroy',$type) }}" class="d-inline" onsubmit="return confirm('Delete this expense type?')">@csrf<button class="btn btn-sm btn-danger">Delete</button></form>
                        </td></tr>
                @empty<tr><td colspan="4" class="text-center text-muted">No expense type found.</td></tr>@endforelse
                </tbody></table>
            </div></div></div>
        </div>
    </div>
</div>
@endsection
