@extends('backend.master')

@section('maincontent')

<style>
.section-box{
    background:#fff;
    padding:18px;
    border-radius:14px;
    border:1px solid #eee;
    margin-bottom:15px;
}
.section-title{
    font-weight:600;
    margin-bottom:10px;
}
</style>

<div class="nxl-content">

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <ul class="breadcrumb mt-1">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('org.manage.index') }}">Organizations</a></li>
                <li class="breadcrumb-item active">Quick Edit</li>
            </ul>
        </div>
    </div>

</div>

<div class="main-content">

<form id="quickForm" method="POST" enctype="multipart/form-data">
@csrf

<div id="deletedContactBox"></div>

<div class="section-box">

    <div class="section-title">🏢 Organization Information</div>

    <div class="row g-2">

        <div class="col-md-4">
            <label>Category</label>
            <select name="organization_category_id" class="form-control">
                <option value="">Select Category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ $org->organization_category_id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Type</label>
            <select name="organization_type_id" class="form-control">
                <option value="">Select Type</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}" {{ $org->organization_type_id == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active" {{ $org->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $org->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Name *</label>
            <input type="text" name="name" value="{{ $org->name }}" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Address</label>
            <input type="text" name="address" value="{{ $org->address }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label>Division</label>
            <select name="division_id" id="division_id" class="form-control">
                <option value="">Select Division</option>
                @foreach($divisions as $d)
                    <option value="{{ $d->id }}" {{ $org->division_id == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-control">
                <option value="">Select District</option>
                @foreach($districts as $d)
                    <option value="{{ $d->id }}" {{ $org->district_id == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Upazila</label>
            <select name="upazila_id" id="upazila_id" class="form-control">
                <option value="">Select Upazila</option>
                @foreach($upazilas as $u)
                    <option value="{{ $u->id }}" {{ $org->upazila_id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Union</label>
            <select name="union_id" id="union_id" class="form-control">
                <option value="">Select Union</option>
                @foreach($unions as $u)
                    <option value="{{ $u->id }}" {{ $org->union_id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>No. of Beds</label>
            <input type="number" min="0" name="no_of_beds" class="form-control" value="{{ $org->no_of_beds }}" placeholder="Hospital size">
        </div>

        <div class="col-md-4">
            <label>Phone Primary</label>
            <input type="text" name="phone_primary" value="{{ $org->phone_primary }}" class="form-control">
        </div>

        <div class="col-md-4">
            <label>Phone Secondary</label>
            <input type="text" name="phone_secondary" value="{{ $org->phone_secondary }}" class="form-control">
        </div>

        <div class="col-md-4">
            <label>Email</label>
            <input type="email" name="email" value="{{ $org->email }}" class="form-control">
        </div>

        <div class="col-md-6">
            <label>Website</label>
            <input type="text" name="website" value="{{ $org->website }}" class="form-control">
        </div>

        <div class="col-md-6">
            <label>Map Location Link</label>
            <div class="input-group">
                <input type="url" name="map_location_link" value="{{ $org->map_location_link }}" class="form-control" placeholder="https://maps.app.goo.gl/...">
                @if($org->map_location_link)
                    <a href="{{ $org->map_location_link }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                        <i class="feather-map-pin"></i> View Map
                    </a>
                @endif
            </div>
        </div>

        <div class="col-md-12">
            <label>About Organization</label>
            <textarea name="about_us" class="form-control">{{ $org->about_us }}</textarea>
        </div>

        <div class="col-md-12">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ $org->notes }}</textarea>
        </div>

    </div>
</div>

<div class="section-box">

    <div class="d-flex justify-content-between">
        <div class="section-title">👤 Contacts</div>
        <button type="button" id="addMore" class="btn btn-sm btn-primary">+ Add Contact</button>
    </div>

    <div id="contactBox">

        @forelse($org->contacts as $key => $contact)

        <div class="contact-row border p-2 mt-2">
            <input type="hidden" name="contacts[{{ $key }}][id]" value="{{ $contact->id }}">

            <div class="row g-2">

                <div class="col-md-3">
                    <input name="contacts[{{ $key }}][title]" value="{{ $contact->title }}" class="form-control" placeholder="Title">
                </div>

                <div class="col-md-3">
                    <input name="contacts[{{ $key }}][name]" value="{{ $contact->name }}" class="form-control" placeholder="Full Name">
                </div>

                <div class="col-md-3">
                    <input name="contacts[{{ $key }}][phone]" value="{{ $contact->phone }}" class="form-control" placeholder="Phone">
                </div>

                <div class="col-md-3">
                    <input name="contacts[{{ $key }}][email]" value="{{ $contact->email }}" class="form-control" placeholder="Email">
                </div>

                <div class="col-md-3">
                    <select name="contacts[{{ $key }}][department_id]" class="form-control">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $contact->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="contacts[{{ $key }}][designation_id]" class="form-control">
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->id }}" {{ $contact->designation_id == $designation->id ? 'selected' : '' }}>
                                {{ $designation->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="file" name="contacts[{{ $key }}][image]" class="form-control">
                    @if($contact->image_url)
                        <small>
                            <a href="{{ asset($contact->image_url) }}" target="_blank">View Image</a>
                        </small>
                    @endif
                </div>

                <div class="col-md-3">
                    <select name="contacts[{{ $key }}][status]" class="form-control">
                        <option value="active" {{ $contact->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $contact->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <input name="contacts[{{ $key }}][phone_two]" value="{{ $contact->phone_two }}" class="form-control" placeholder="Secondary Phone">
                </div>

                <div class="col-md-6">
                    <input name="contacts[{{ $key }}][address]" value="{{ $contact->address }}" class="form-control" placeholder="Address">
                </div>

                <div class="col-md-9">
                    <input name="contacts[{{ $key }}][additional_info]" value="{{ $contact->additional_info }}" class="form-control" placeholder="Additional Info">
                </div>

                <div class="col-md-2 mt-2">
                    <label>
                        <input type="checkbox" name="contacts[{{ $key }}][is_primary]" value="1" {{ $contact->is_primary ? 'checked' : '' }}>
                        K.O.L
                    </label>
                </div>

                <div class="col-md-1 mt-1">
                    <button type="button" class="btn btn-sm btn-danger btnRemoveContact" data-id="{{ $contact->id }}">X</button>
                </div>

            </div>
        </div>

        @empty

        <div class="contact-row border p-2 mt-2">
            <div class="row g-2">

                <div class="col-md-3">
                    <input name="contacts[0][title]" class="form-control" placeholder="Title">
                </div>

                <div class="col-md-3">
                    <input name="contacts[0][name]" class="form-control" placeholder="Full Name">
                </div>

                <div class="col-md-3">
                    <input name="contacts[0][phone]" class="form-control" placeholder="Phone">
                </div>

                <div class="col-md-3">
                    <input name="contacts[0][email]" class="form-control" placeholder="Email">
                </div>

            </div>
        </div>

        @endforelse

    </div>

</div>

<button type="submit" class="btn btn-success">Update All</button>

</form>

<br><br><br>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let i = {{ $org->contacts->count() > 0 ? $org->contacts->count() : 1 }};

$('#addMore').on('click', function(){

    let html = `
    <div class="contact-row border p-2 mt-2">
        <div class="row g-2">

            <div class="col-md-3">
                <input name="contacts[${i}][title]" class="form-control" placeholder="Title">
            </div>

            <div class="col-md-3">
                <input name="contacts[${i}][name]" class="form-control" placeholder="Full Name">
            </div>

            <div class="col-md-3">
                <input name="contacts[${i}][phone]" class="form-control" placeholder="Phone">
            </div>

            <div class="col-md-3">
                <input name="contacts[${i}][email]" class="form-control" placeholder="Email">
            </div>

            <div class="col-md-3">
                <select name="contacts[${i}][department_id]" class="form-control">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="contacts[${i}][designation_id]" class="form-control">
                    <option value="">Select Designation</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <input type="file" name="contacts[${i}][image]" class="form-control">
            </div>

            <div class="col-md-3">
                <select name="contacts[${i}][status]" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-md-6">
                <input name="contacts[${i}][phone_two]" class="form-control" placeholder="Secondary Phone">
            </div>

            <div class="col-md-6">
                <input name="contacts[${i}][address]" class="form-control" placeholder="Address">
            </div>

            <div class="col-md-9">
                <input name="contacts[${i}][additional_info]" class="form-control" placeholder="Additional Info">
            </div>

            <div class="col-md-2 mt-2">
                <label>
                    <input type="checkbox" name="contacts[${i}][is_primary]" value="1">
                    K.O.L
                </label>
            </div>

            <div class="col-md-1 mt-1">
                <button type="button" class="btn btn-sm btn-danger btnRemoveContact">X</button>
            </div>

        </div>
    </div>`;

    $('#contactBox').append(html);
    i++;
});

$(document).on('click', '.btnRemoveContact', function(){

    let id = $(this).data('id');

    if(id){
        $('#deletedContactBox').append(
            `<input type="hidden" name="deleted_contacts[]" value="${id}">`
        );
    }

    $(this).closest('.contact-row').remove();
});

$('#division_id').on('change', function () {

    let id = $(this).val();

    $('#district_id').html('<option>Loading...</option>');
    $('#upazila_id').html('<option value="">Select Upazila</option>');
    $('#union_id').html('<option value="">Select Union</option>');

    if(!id) return;

    $.get("{{ route('org.geo.districts') }}", {division_id:id}, function(res){

        let html = '<option value="">Select District</option>';

        res.data.forEach(function(item){
            html += `<option value="${item.id}">${item.name}</option>`;
        });

        $('#district_id').html(html);
    });
});

$('#district_id').on('change', function () {

    let id = $(this).val();

    $('#upazila_id').html('<option>Loading...</option>');
    $('#union_id').html('<option value="">Select Union</option>');

    if(!id) return;

    $.get("{{ route('org.geo.upazilas') }}", {district_id:id}, function(res){

        let html = '<option value="">Select Upazila</option>';

        res.data.forEach(function(item){
            html += `<option value="${item.id}">${item.name}</option>`;
        });

        $('#upazila_id').html(html);
    });
});

$('#upazila_id').on('change', function () {

    let id = $(this).val();

    if(!id) return;

    $.get("{{ route('org.geo.unions') }}", {upazila_id:id}, function(res){

        let html = '<option value="">Select Union</option>';

        res.data.forEach(function(item){
            html += `<option value="${item.id}">${item.name}</option>`;
        });

        $('#union_id').html(html);
    });
});

$('#quickForm').on('submit', function(e){
    e.preventDefault();

    let form = $('#quickForm')[0];
    let formData = new FormData(form);
    let btn = $('#quickForm').find('button[type="submit"]');

    btn.prop('disabled', true).text('Updating...');

    $.ajax({
        url: "{{ route('org.manage.update', $org->id) }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(res){

            btn.prop('disabled', false).text('Update All');

            if(res.status){
                Swal.fire('Success', res.message ?? 'Updated', 'success');
            }else{
                Swal.fire('Error', 'Something went wrong!', 'error');
            }
        },

        error: function(err){

            btn.prop('disabled', false).text('Update All');

            if(err.status === 422){

                let errors = err.responseJSON.errors;
                let msg = '';

                $.each(errors, function(key, value){
                    msg += value[0] + "\n";
                });

                Swal.fire('Error', msg, 'error');

            }else{
                Swal.fire('Error', 'Server Error!', 'error');
                console.log(err.responseText);
            }
        }
    });

});
</script>

@endsection