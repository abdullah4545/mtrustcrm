@extends('backend.master')
@section('maincontent')
@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }}-Social Settings
@endsection

<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Business</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li> 
                <li class="breadcrumb-item">Social</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" id="btnSave" class="btn btn-primary">
                    <i class="feather-save me-2"></i>
                    <span>Update Social</span>
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-xl-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <h5>Social Links</h5>
                    </div>
                    <div class="card-body">

                        <form id="settingsForm">
                            @csrf

                            <div class="row">

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Facebook</label>
                                        <input type="text" class="form-control" name="facebook"
                                               value="{{ $business->facebook ?? '' }}"
                                               placeholder="https://facebook.com/yourpage">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Instagram</label>
                                        <input type="text" class="form-control" name="instagram"
                                               value="{{ $business->instagram ?? '' }}"
                                               placeholder="https://instagram.com/yourprofile">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Twitter / X</label>
                                        <input type="text" class="form-control" name="twitter"
                                               value="{{ $business->twitter ?? '' }}"
                                               placeholder="https://x.com/yourprofile">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">LinkedIn</label>
                                        <input type="text" class="form-control" name="linkedin"
                                               value="{{ $business->linkedin ?? '' }}"
                                               placeholder="https://linkedin.com/company/yourcompany">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">YouTube</label>
                                        <input type="text" class="form-control" name="youtube"
                                               value="{{ $business->youtube ?? '' }}"
                                               placeholder="https://youtube.com/@yourchannel">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">WhatsApp</label>
                                        <input type="text" class="form-control" name="whatsapp"
                                               value="{{ $business->whatsapp ?? '' }}"
                                               placeholder="+8801XXXXXXXXX or https://wa.me/8801XXXXXXXXX">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">TikTok</label>
                                        <input type="text" class="form-control" name="tiktok"
                                               value="{{ $business->tiktok ?? '' }}"
                                               placeholder="https://tiktok.com/@yourprofile">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Pinterest</label>
                                        <input type="text" class="form-control" name="pinterest"
                                               value="{{ $business->pinterest ?? '' }}"
                                               placeholder="https://pinterest.com/yourprofile">
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
const UPDATE_URL = "{{ route('settings.socialupdate') }}";

$(document).on('click', '#btnSave', function(e){
    e.preventDefault();

    let fd = new FormData(document.getElementById('settingsForm'));

    $.ajax({
        url: UPDATE_URL,
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function(res){
            if(window.Swal){
                Swal.fire('Success', res.message ?? 'Updated', 'success');
            }else{
                alert(res.message ?? 'Updated');
            }
        },
        error: function(xhr){
            let msg = 'Something went wrong';
            if(xhr.status === 419) msg = 'CSRF token mismatch (419).';
            else if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

            if(window.Swal){
                Swal.fire('Error', msg, 'error');
            }else{
                alert(msg);
            }
        }
    });
});
</script>
@endpush
