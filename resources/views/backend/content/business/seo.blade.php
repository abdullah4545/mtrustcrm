@extends('backend.master')

@section('maincontent')
@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }}-SEO Settings
@endsection

<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Business</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li> 
                <li class="breadcrumb-item">SEO</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" id="btnSave" class="btn btn-primary">
                    <i class="feather-save me-2"></i>
                    <span>Update SEO</span>
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-xl-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <h5>SEO Information</h5>
                    </div>
                    <div class="card-body">

                        <form id="settingsForm" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Site Title</label>
                                        <input type="text" class="form-control" name="title"
                                               value="{{ $business->title ?? '' }}">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" name="meta_title"
                                               value="{{ $business->meta_title ?? '' }}">
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Meta Description</label>
                                        <textarea rows="4" class="form-control" name="meta_description"
                                                  placeholder="Enter meta description...">{{ $business->meta_description ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Meta Keywords</label>
                                        <textarea rows="3" class="form-control" name="meta_keywords"
                                                  placeholder="keyword1, keyword2, keyword3">{{ $business->meta_keywords ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Meta Image</label>
                                        <input type="file" class="form-control" name="meta_image" accept="image/*">
                                        @if(!empty($business->meta_image))
                                            <img class="mt-2" src="{{ asset($business->meta_image) }}" style="height:80px;">
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
const UPDATE_URL = "{{ route('settings.seoupdate') }}";

$(document).on('click', '#btnSave', function(e){
    e.preventDefault();

    let form = document.getElementById('settingsForm');
    let fd = new FormData(form);

    $.ajax({
        url: UPDATE_URL,
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function(res){
            if(window.Swal){
                Swal.fire('Success', res.message ?? 'Updated', 'success');
                location.reload();
            }else{
                alert(res.message ?? 'Updated');
            }
        },
        error: function(xhr){
            let msg = 'Something went wrong';
            if (xhr.status === 419) msg = 'CSRF token mismatch (419).';
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
