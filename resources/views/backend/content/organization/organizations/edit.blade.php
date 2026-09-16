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

        @php($orgPhones = $org->phone_numbers ?: array_values(array_filter([$org->phone_primary,$org->phone_secondary])))
        @php($orgEmails = $org->email_addresses ?: array_values(array_filter([$org->email])))
        <div class="col-md-6"><label>Phone</label><div id="orgPhoneList">@forelse($orgPhones as $k=>$v)<div class="repeat-field mb-1"><div class="input-group"><input type="text" name="phone_numbers[]" value="{{ $v }}" class="form-control" placeholder="Phone">@if($k===0)<button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="phone">+</button>@else<button type="button" class="btn btn-outline-danger org-remove-repeat">−</button>@endif</div></div>@empty<div class="repeat-field"><div class="input-group"><input type="text" name="phone_numbers[]" class="form-control" placeholder="Phone"><button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="phone">+</button></div></div>@endforelse</div></div>
        <div class="col-md-6"><label>Email</label><div id="orgEmailList">@forelse($orgEmails as $k=>$v)<div class="repeat-field mb-1"><div class="input-group"><input type="email" name="email_addresses[]" value="{{ $v }}" class="form-control" placeholder="Email">@if($k===0)<button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="email">+</button>@else<button type="button" class="btn btn-outline-danger org-remove-repeat">−</button>@endif</div></div>@empty<div class="repeat-field"><div class="input-group"><input type="email" name="email_addresses[]" class="form-control" placeholder="Email"><button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="email">+</button></div></div>@endforelse</div></div>

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
            <div class="mt-3"><label>Existing Machine *</label><textarea id="existing_machine_editor" name="existing_machine" class="form-control" rows="5" required>{{ $org->existing_machine }}</textarea></div>
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
                    @php($phones = $contact->phone_numbers ?: array_values(array_filter([$contact->phone, $contact->phone_two])))
                    <div class="repeat-list" data-kind="phone">
                    @forelse($phones as $phoneIndex => $phone)
                        <div class="repeat-field mb-1"><div class="input-group"><input name="contacts[{{ $key }}][phone_numbers][]" value="{{ $phone }}" class="form-control" placeholder="Phone">@if($phoneIndex===0)<button type="button" class="btn btn-outline-primary add-repeat" data-kind="phone">+</button>@else<button type="button" class="btn btn-outline-danger remove-repeat">−</button>@endif</div></div>
                    @empty
                        <div class="repeat-field"><div class="input-group"><input name="contacts[{{ $key }}][phone_numbers][]" class="form-control" placeholder="Phone"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="phone">+</button></div></div>
                    @endforelse
                    </div>
                </div>

                <div class="col-md-3">
                    @php($emails = $contact->email_addresses ?: array_values(array_filter([$contact->email])))
                    <div class="repeat-list" data-kind="email">
                    @forelse($emails as $emailIndex => $email)
                        <div class="repeat-field mb-1"><div class="input-group"><input type="email" name="contacts[{{ $key }}][email_addresses][]" value="{{ $email }}" class="form-control" placeholder="Email">@if($emailIndex===0)<button type="button" class="btn btn-outline-primary add-repeat" data-kind="email">+</button>@else<button type="button" class="btn btn-outline-danger remove-repeat">−</button>@endif</div></div>
                    @empty
                        <div class="repeat-field"><div class="input-group"><input type="email" name="contacts[{{ $key }}][email_addresses][]" class="form-control" placeholder="Email"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="email">+</button></div></div>
                    @endforelse
                    </div>
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
                    <label class="form-label">Visiting Card</label><input type="file" name="contacts[{{ $key }}][image]" class="form-control">
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
                    <small class="text-muted">Use + beside Phone to add more numbers.</small>
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

        <div id="noContactState" class="text-center text-muted py-4">
            <i class="feather-user-plus d-block mb-2" style="font-size:26px;"></i>
            <div>No contact added yet.</div>
            <small>Click <strong>+ Add Contact</strong> to add a contact person.</small>
        </div>

        @endforelse

    </div>

</div>

<button type="submit" class="btn btn-success">Update All</button>

</form>

<br><br><br>

</div>


@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
let existingMachineEditor = null;
ClassicEditor.create(document.querySelector('#existing_machine_editor'), {toolbar:['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo']}).then(editor=>{ existingMachineEditor=editor; }).catch(console.error);
$(document).on('click','.org-add-repeat',function(){ const kind=$(this).data('kind'), list=kind==='phone'?'#orgPhoneList':'#orgEmailList', type=kind==='email'?'email':'text', name=kind==='email'?'email_addresses[]':'phone_numbers[]'; $(list).append(`<div class="repeat-field mt-1"><div class="input-group"><input type="${type}" name="${name}" class="form-control" placeholder="Additional ${kind==='email'?'Email':'Phone'}"><button type="button" class="btn btn-outline-danger org-remove-repeat">−</button></div></div>`); });
$(document).on('click','.org-remove-repeat',function(){ $(this).closest('.repeat-field').remove(); });
let i = {{ $org->contacts->count() }};


function repeatInputName(row, kind){
    const any = row.find(kind==='phone' ? 'input[name*=\"[phone_numbers]\"]' : 'input[name*=\"[email_addresses]\"]').first();
    return any.attr('name') || '';
}
$(document).on('click','.add-repeat',function(){
    const row=$(this).closest('.contact-row'); const kind=$(this).data('kind'); const name=repeatInputName(row,kind);
    if(!name) return;
    const type=kind==='email'?'email':'text'; const placeholder=kind==='email'?'Additional Email':'Additional Phone';
    const html=`<div class="repeat-field mt-1"><div class="input-group"><input type="${type}" name="${name}" class="form-control" placeholder="${placeholder}"><button type="button" class="btn btn-outline-danger remove-repeat">−</button></div></div>`;
    $(this).closest('.repeat-field').after(html);
});
$(document).on('click','.remove-repeat',function(){ $(this).closest('.repeat-field').remove(); });

$('#addMore').on('click', function(){

    $('#noContactState').remove();

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
                <div class="repeat-field" data-type="phone"><div class="input-group"><input name="contacts[${i}][phone_numbers][]" class="form-control" placeholder="Phone"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="phone">+</button></div></div>
            </div>

            <div class="col-md-3">
                <div class="repeat-field" data-type="email"><div class="input-group"><input type="email" name="contacts[${i}][email_addresses][]" class="form-control" placeholder="Email"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="email">+</button></div></div>
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
                <label class="form-label">Visiting Card</label><input type="file" name="contacts[${i}][image]" class="form-control">
            </div>

            <div class="col-md-3">
                <select name="contacts[${i}][status]" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-md-6">
                
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

    if($('#contactBox .contact-row').length === 0){
        $('#contactBox').html(`
            <div id="noContactState" class="text-center text-muted py-4">
                <i class="feather-user-plus d-block mb-2" style="font-size:26px;"></i>
                <div>No contact added yet.</div>
                <small>Click <strong>+ Add Contact</strong> to add a contact person.</small>
            </div>
        `);
    }
});

// Organization Edit: same working dependent geo chain as Quick Create.
// Existing saved values stay visible on initial load. Changing a parent reloads only its children.
(function () {
    const districtUrl = @json(route('org.geo.districts'));
    const upazilaUrl  = @json(route('org.geo.upazilas'));
    const unionUrl    = @json(route('org.geo.unions'));

    function setOptions(selector, placeholder, rows) {
        const $el = $(selector);
        let html = `<option value="">${placeholder}</option>`;
        (rows || []).forEach(function (row) {
            html += `<option value="${row.id}">${$('<div>').text(row.name).html()}</option>`;
        });
        $el.html(html).prop('disabled', false).trigger('change.select2');
    }

    function setLoading(selector, text) {
        $(selector).html(`<option value="">${text}</option>`).prop('disabled', true).trigger('change.select2');
    }

    function getRows(url, params, done, failed) {
        $.ajax({
            url: url,
            type: 'GET',
            data: params,
            dataType: 'json',
            cache: false,
            success: function (res) {
                const rows = Array.isArray(res) ? res : (Array.isArray(res.data) ? res.data : []);
                done(rows);
            },
            error: function (xhr) {
                console.error('Organization edit geo load failed:', xhr.status, xhr.responseText);
                failed();
            }
        });
    }

    $(document).off('change.orgGeoEdit', '#division_id').on('change.orgGeoEdit', '#division_id', function () {
        const id = $(this).val();
        setOptions('#district_id', 'Select District', []);
        setOptions('#upazila_id', 'Select Upazila', []);
        setOptions('#union_id', 'Select Union', []);
        if (!id) return;

        setLoading('#district_id', 'Loading District...');
        getRows(districtUrl, { division_id: id }, function (rows) {
            setOptions('#district_id', 'Select District', rows);
        }, function () {
            setOptions('#district_id', 'Select District', []);
        });
    });

    $(document).off('change.orgGeoEdit', '#district_id').on('change.orgGeoEdit', '#district_id', function () {
        const id = $(this).val();
        setOptions('#upazila_id', 'Select Upazila', []);
        setOptions('#union_id', 'Select Union', []);
        if (!id) return;

        setLoading('#upazila_id', 'Loading Upazila...');
        getRows(upazilaUrl, { district_id: id }, function (rows) {
            setOptions('#upazila_id', 'Select Upazila', rows);
        }, function () {
            setOptions('#upazila_id', 'Select Upazila', []);
        });
    });

    $(document).off('change.orgGeoEdit', '#upazila_id').on('change.orgGeoEdit', '#upazila_id', function () {
        const id = $(this).val();
        setOptions('#union_id', 'Select Union', []);
        if (!id) return;

        setLoading('#union_id', 'Loading Union...');
        getRows(unionUrl, { upazila_id: id }, function (rows) {
            setOptions('#union_id', 'Select Union', rows);
        }, function () {
            setOptions('#union_id', 'Select Union', []);
        });
    });
})();

$('#quickForm').on('submit', function(e){
    e.preventDefault();

    if(existingMachineEditor){ document.querySelector('#existing_machine_editor').value = existingMachineEditor.getData(); }
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
@endpush

@endsection