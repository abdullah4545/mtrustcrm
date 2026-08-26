@extends('backend.master')
@section('maincontent')
@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }}-Business Settings
@endsection

<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Business</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" id="btnSave" class="btn btn-primary">
                    <i class="feather-save me-2"></i>
                    <span>Update Settings</span>
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-xl-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div><h5 class="mb-1">Company & Brand Identity</h5><small class="text-muted">Used across CRM, login, invoices and quotations.</small></div>
                    </div>
                    <div class="card-body">

                        <form id="settingsForm" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Business Name *</label>
                                        <input type="text" class="form-control" name="business_name"
                                               value="{{ $business->business_name }}" required>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Business Email</label>
                                        <input type="email" class="form-control" name="business_email"
                                               value="{{ $business->business_email }}">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="business_phone"
                                               value="{{ $business->business_phone }}">
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">VAT / Tax (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="vat"
                                               value="{{ $business->vat ?? 0 }}">
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="business_address"
                                               value="{{ $business->business_address }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-dashed">

                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Timezone</label>
                                        <input type="text" class="form-control" name="timezone"
                                               value="{{ $business->timezone ?? 'Asia/Dhaka' }}">
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Currency</label>
                                        <input type="text" class="form-control" name="currency"
                                               value="{{ $business->currency ?? 'BDT' }}">
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Currency Symbol</label>
                                        <input type="text" class="form-control" name="currency_symbol"
                                               value="{{ $business->currency_symbol ?? '৳' }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-dashed">

                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Logo</label>
                                        <input type="file" class="form-control" name="logo" accept="image/*">
                                        @if($business->logo)<div class="mt-2 p-2 border rounded bg-white"><img src="{{ asset($business->logo) }}" style="height:58px;max-width:100%;object-fit:contain;"></div>@endif
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Favicon</label>
                                        <input type="file" class="form-control" name="fav_icon" accept="image/*">
                                        @if($business->fav_icon)
                                            <img class="mt-2" src="{{ asset($business->fav_icon) }}" style="height:40px;">
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

             

        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
    

    $(document).ready(function () {

        $(document).on('click', '#btnSave', function(e){
            e.preventDefault();
            

            let form = document.getElementById('settingsForm');
            let fd = new FormData(form);

            $.ajax({
                url: "{{ route('settings.update') }}",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res){
                    
                    if(window.Swal){
                        Swal.fire('Success', res.message ?? 'Updated', 'success');
                        location.reload();
                    } else {
                        alert(res.message ?? 'Updated');
                    }
                },
                error: function(xhr){
                    
                    let msg = 'Something went wrong';
                    if (xhr.status === 419) msg = 'CSRF token mismatch (419).';
                    else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                    if(window.Swal){
                        Swal.fire('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            });
        });

    });
    </script>
@endpush 