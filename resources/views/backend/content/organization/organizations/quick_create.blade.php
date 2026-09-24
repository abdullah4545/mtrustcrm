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
            <label>Category Type <span class="text-danger">*</span></label>
            <select name="organization_category_id" class="form-control" required>
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
            <label>Division <span class="text-danger">*</span></label>
            <select name="division_id" id="division_id" class="form-control" required>
                <option value="">Select Division</option>
                @foreach($divisions as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>District <span class="text-danger">*</span></label>
            <select name="district_id" id="district_id" class="form-control" required>
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

        <div class="col-md-6"><label>Phone</label><div id="orgPhoneList"><div class="repeat-field"><div class="input-group"><input type="text" name="phone_numbers[]" class="form-control" placeholder="01XXXXXXXXX"><button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="phone">+</button></div></div></div></div>
        <div class="col-md-6"><label>Email</label><div id="orgEmailList"><div class="repeat-field"><div class="input-group"><input type="email" name="email_addresses[]" class="form-control" placeholder="example@mail.com"><button type="button" class="btn btn-outline-primary org-add-repeat" data-kind="email">+</button></div></div></div></div>

        <div class="col-md-6">
            <label>Website</label>
            <input type="text" name="website" class="form-control" placeholder="https://">
        </div>

        <div class="col-md-6">
            <label>Map Location Link</label>
            <input type="url" name="map_location_link" class="form-control" placeholder="https://maps.app.goo.gl/...">
            <small class="text-muted">Paste the Google Maps/location share link for this organization.</small>
        </div>

        <div class="col-md-12">
            <label>About Organization</label>
            <textarea name="about_us" class="form-control" placeholder="Write something about this Organization..."></textarea>
            <div class="mt-3"><label>Existing Machine *</label><textarea id="existing_machine_editor" name="existing_machine" class="form-control" rows="5" placeholder="Existing machine / equipment details"></textarea></div>
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
                    <div class="repeat-field" data-type="phone"><div class="input-group"><input name="contacts[0][phone_numbers][]" class="form-control" placeholder="Phone"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="phone">+</button></div></div>
                </div>

                <div class="col-md-3">
                    <div class="repeat-field" data-type="email"><div class="input-group"><input type="email" name="contacts[0][email_addresses][]" class="form-control" placeholder="Email"><button type="button" class="btn btn-outline-primary add-repeat" data-kind="email">+</button></div></div>
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
                    <label class="form-label">Visiting Card</label><input type="file" name="contacts[0][image]" class="form-control">
                </div>

                <div class="col-md-3">
                    <select name="contacts[0][status]" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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



@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
let existingMachineEditor = null;
ClassicEditor.create(document.querySelector('#existing_machine_editor'), {toolbar:['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo']}).then(editor=>{ existingMachineEditor=editor; }).catch(console.error);
$(document).on('click','.org-add-repeat',function(){ const kind=$(this).data('kind'), list=kind==='phone'?'#orgPhoneList':'#orgEmailList', type=kind==='email'?'email':'text', name=kind==='email'?'email_addresses[]':'phone_numbers[]'; $(list).append(`<div class="repeat-field mt-1"><div class="input-group"><input type="${type}" name="${name}" class="form-control" placeholder="Additional ${kind==='email'?'Email':'Phone'}"><button type="button" class="btn btn-outline-danger org-remove-repeat">−</button></div></div>`); });
$(document).on('click','.org-remove-repeat',function(){ $(this).closest('.repeat-field').remove(); });
let i = 1;


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

// Organization Create: dependent geo dropdowns.
// Delegated events are used so Select2/re-rendering cannot break the handlers.
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
                console.error('Organization geo load failed:', xhr.status, xhr.responseText);
                failed();
            }
        });
    }

    $(document).off('change.orgGeo', '#division_id').on('change.orgGeo', '#division_id', function () {
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

    $(document).off('change.orgGeo', '#district_id').on('change.orgGeo', '#district_id', function () {
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

    $(document).off('change.orgGeo', '#upazila_id').on('change.orgGeo', '#upazila_id', function () {
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

    if(existingMachineEditor){
        const existingMachineValue = existingMachineEditor.getData().trim();
        document.querySelector('#existing_machine_editor').value = existingMachineValue;
        if (!existingMachineValue || existingMachineValue === '<p>&nbsp;</p>') {
            e.preventDefault();
            Swal.fire('Validation Error', 'Existing Machine is required.', 'warning');
            existingMachineEditor.editing.view.focus();
            return false;
        }
    }
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
@endpush
