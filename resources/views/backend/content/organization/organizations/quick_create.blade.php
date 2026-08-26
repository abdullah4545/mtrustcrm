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
                <li class="breadcrumb-item active">Quick Create</li>
            </ul>
        </div>
    </div>

</div>

<div class="main-content">

<form id="quickForm" method="POST" enctype="multipart/form-data">
@csrf

<div class="section-box">

    <div class="section-title">🏢 Organization Information</div>

    <div class="row g-2">

        <div class="col-md-4">
            <label>Category</label>
            <select name="organization_category_id" class="form-control">
                <option value="">Select Category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Type</label>
            <select name="organization_type_id" class="form-control">
                <option value="">Select Type</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Organization Name" required>
        </div>

        <div class="col-md-6">
            <label>Address</label>
            <input type="text" name="address" class="form-control" placeholder="Full Address">
        </div>

        <div class="col-md-3">
            <label>Division</label>
            <select name="division_id" id="division_id" class="form-control">
                <option value="">Select Division</option>
                @foreach($divisions as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-control">
                <option value="">Select District</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Upazila</label>
            <select name="upazila_id" id="upazila_id" class="form-control">
                <option value="">Select Upazila</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Union</label>
            <select name="union_id" id="union_id" class="form-control">
                <option value="">Select Union</option>
            </select>
        </div>

        <div class="col-md-4">
            <label>No. of Beds</label>
            <input type="number" min="0" name="no_of_beds" class="form-control" placeholder="Hospital size">
        </div>

        <div class="col-md-4">
            <label>Phone Primary</label>
            <input type="text" name="phone_primary" class="form-control" placeholder="01XXXXXXXXX">
        </div>

        <div class="col-md-4">
            <label>Phone Secondary</label>
            <input type="text" name="phone_secondary" class="form-control" placeholder="Optional">
        </div>

        <div class="col-md-4">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="example@mail.com">
        </div>

        <div class="col-md-6">
            <label>Website</label>
            <input type="text" name="website" class="form-control" placeholder="https://">
        </div>

        <div class="col-md-3">
            <label>Latitude</label>
            <input type="text" name="latitude" class="form-control" placeholder="23.XXXX">
        </div>

        <div class="col-md-3">
            <label>Longitude</label>
            <input type="text" name="longitude" class="form-control" placeholder="90.XXXX">
        </div>

        <div class="col-md-12">
            <label>About Organization</label>
            <textarea name="about_us" class="form-control" placeholder="Write something about this Organization..."></textarea>
        </div>

        <div class="col-md-12">
            <label>Notes</label>
            <textarea name="notes" class="form-control" placeholder="Write notes..."></textarea>
        </div>

    </div>
</div>

<div class="section-box">

    <div class="d-flex justify-content-between">
        <div class="section-title">👤 Contacts</div>
        <button type="button" id="addMore" class="btn btn-sm btn-primary">+ Add Contact</button>
    </div>

    <div id="contactBox">

        <div class="contact-row border p-2 mt-2">
            <div class="row g-2">

                <div class="col-md-3">
                    <input name="contacts[0][title]" class="form-control" placeholder="Title (Mr/Ms/Dr)">
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

                <div class="col-md-3">
                    <select name="contacts[0][department_id]" class="form-control">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="contacts[0][designation_id]" class="form-control">
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="file" name="contacts[0][image]" class="form-control">
                </div>

                <div class="col-md-3">
                    <select name="contacts[0][status]" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <input name="contacts[0][phone_two]" class="form-control" placeholder="Secondary Phone">
                </div>

                <div class="col-md-6">
                    <input name="contacts[0][address]" class="form-control" placeholder="Address">
                </div>

                <div class="col-md-9">
                    <input name="contacts[0][additional_info]" class="form-control" placeholder="Additional Info">
                </div>

                <div class="col-md-3 mt-2">
                    <label>
                        <input type="checkbox" name="contacts[0][is_primary]" value="1"> K.O.L
                    </label>
                </div>

            </div>
        </div>

    </div>

</div>

<button type="submit" class="btn btn-success">Save All</button>

</form>

<br><br><br>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let i = 1;

$('#addMore').on('click', function(){

    let html = `
    <div class="contact-row border p-2 mt-2">
        <div class="row g-2">

            <div class="col-md-3">
                <input name="contacts[${i}][title]" class="form-control" placeholder="Title (Mr/Ms/Dr)">
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

            <div class="col-md-3 mt-2">
                <label>
                    <input type="checkbox" name="contacts[${i}][is_primary]" value="1"> K.O.L
                </label>
            </div>

        </div>
    </div>`;

    $('#contactBox').append(html);
    i++;
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

    btn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: "{{ route('org.quick.store') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(res){

            btn.prop('disabled', false).text('Save All');

            if(res.status){
                Swal.fire('Success', res.message ?? 'Created', 'success');

                $('#quickForm')[0].reset();
                $('#contactBox').html('');
                i = 0;
                $('#addMore').click();
            }else{
                Swal.fire('Error', 'Something went wrong!', 'error');
            }
        },

        error: function(err){

            btn.prop('disabled', false).text('Save All');

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