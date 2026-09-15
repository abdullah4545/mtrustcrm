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

<form id="quickForm" class="organization-quick-create-form" method="POST" enctype="multipart/form-data">
@csrf

<div class="section-box">

    <div class="section-title">🏢 Organization Information</div>

    <div class="row g-2">

        <div class="col-md-4">
            <label>Category</label>
            <select name="organization_category_id" class="form-control no-select2">
                <option value="">Select Category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Type</label>
            <select name="organization_type_id" class="form-control no-select2">
                <option value="">Select Type</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Status</label>
            <select name="status" class="form-control no-select2">
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
            <select name="division_id" id="division_id" class="form-control no-select2">
                <option value="">Select Division</option>
                @foreach($divisions as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-control no-select2">
                <option value="">Select District</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Upazila</label>
            <select name="upazila_id" id="upazila_id" class="form-control no-select2">
                <option value="">Select Upazila</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Union</label>
            <select name="union_id" id="union_id" class="form-control no-select2">
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

        <div class="col-md-6">
            <label>Map Location Link</label>
            <input type="url" name="map_location_link" class="form-control" placeholder="https://maps.app.goo.gl/...">
            <small class="text-muted">Paste the Google Maps/location share link for this organization.</small>
        </div>

        <div class="col-md-12">
            <label>About Organization</label>
            <textarea name="about_us" class="form-control" placeholder="Write something about this Organization..."></textarea>
            <div class="mt-3"><label>Existing Machine</label><textarea name="existing_machine" class="form-control" rows="3" placeholder="Existing machine / equipment details"></textarea></div>
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
                    <input type="file" name="contacts[0][image]" class="form-control">
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


<style>
/* Quick Create: always keep the Select2 search field visible and usable. */
.select2-container--open { z-index: 99999 !important; }
.select2-search--dropdown { display: block !important; padding: 8px !important; }
.select2-search--dropdown .select2-search__field {
    display: block !important; width: 100% !important; min-height: 38px;
    padding: 6px 10px; border: 1px solid #ced4da; border-radius: 6px;
}
</style>

<script>
// Keep Select2 on Quick Create, but own its lifecycle locally.
// no-select2 prevents the global observer from initializing the same element twice.
$('#quickForm select').addClass('no-select2');

function initQuickSelect2(scope) {
    if (!$.fn.select2) return;
    const $scope = scope ? $(scope) : $('#quickForm');
    $scope.find('select.no-select2').addBack('select.no-select2').each(function () {
        const $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) return;
        $el.select2({
            width: '100%',
            allowClear: false,
            minimumResultsForSearch: 0,
            dropdownParent: $(document.body)
        });
    });
}

initQuickSelect2('#quickForm');

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
                <select name="contacts[${i}][department_id]" class="form-control no-select2">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="contacts[${i}][designation_id]" class="form-control no-select2">
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
                <select name="contacts[${i}][status]" class="form-control no-select2">
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

    const $row = $(html);
    $('#contactBox').append($row);
    initQuickSelect2($row);
    i++;
});

// Reliable dependent geo dropdowns. Requests are aborted when the parent changes
// quickly, so an older/slower response can never overwrite the latest selection.
let geoRequests = { district: null, upazila: null, union: null };

function geoReset($el, placeholder, disabled = false){
    $el.html(`<option value="">${placeholder}</option>`).prop('disabled', disabled);
    if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change.select2');
}

function geoLoading($el){
    $el.html('<option value="">Loading...</option>').prop('disabled', true);
    if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change.select2');
}

function geoFill($el, rows, placeholder){
    let html = `<option value="">${placeholder}</option>`;
    (Array.isArray(rows) ? rows : []).forEach(item => {
        html += `<option value="${item.id}">${$('<div>').text(item.name || '').html()}</option>`;
    });
    $el.html(html).prop('disabled', false);
    if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change.select2');
}

function geoError($el, placeholder, xhr){
    geoReset($el, placeholder, true);
    const message = xhr.status === 403
        ? 'You do not have permission to load this location.'
        : 'Location data could not be loaded. Please try again.';
    if (window.toastr) toastr.error(message); else console.error(message, xhr.responseText || '');
}

$('#division_id').on('change', function () {
    const id = $(this).val();
    if (geoRequests.district) geoRequests.district.abort();
    if (geoRequests.upazila) geoRequests.upazila.abort();
    if (geoRequests.union) geoRequests.union.abort();

    geoReset($('#upazila_id'), 'Select Upazila', true);
    geoReset($('#union_id'), 'Select Union', true);
    if (!id) return geoReset($('#district_id'), 'Select District', true);

    geoLoading($('#district_id'));
    geoRequests.district = $.ajax({
        url: "{{ route('org.geo.districts') }}", data: {division_id:id}, dataType:'json'
    }).done(res => geoFill($('#district_id'), res.data || res, 'Select District'))
      .fail((xhr, status) => { if(status !== 'abort') geoError($('#district_id'), 'Select District', xhr); })
      .always(() => { geoRequests.district = null; });
});

$('#district_id').on('change', function () {
    const id = $(this).val();
    if (geoRequests.upazila) geoRequests.upazila.abort();
    if (geoRequests.union) geoRequests.union.abort();

    geoReset($('#union_id'), 'Select Union', true);
    if (!id) return geoReset($('#upazila_id'), 'Select Upazila', true);

    geoLoading($('#upazila_id'));
    geoRequests.upazila = $.ajax({
        url: "{{ route('org.geo.upazilas') }}", data: {district_id:id}, dataType:'json'
    }).done(res => geoFill($('#upazila_id'), res.data || res, 'Select Upazila'))
      .fail((xhr, status) => { if(status !== 'abort') geoError($('#upazila_id'), 'Select Upazila', xhr); })
      .always(() => { geoRequests.upazila = null; });
});

$('#upazila_id').on('change', function () {
    const id = $(this).val();
    if (geoRequests.union) geoRequests.union.abort();
    if (!id) return geoReset($('#union_id'), 'Select Union', true);

    geoLoading($('#union_id'));
    geoRequests.union = $.ajax({
        url: "{{ route('org.geo.unions') }}", data: {upazila_id:id}, dataType:'json'
    }).done(res => geoFill($('#union_id'), res.data || res, 'Select Union'))
      .fail((xhr, status) => { if(status !== 'abort') geoError($('#union_id'), 'Select Union', xhr); })
      .always(() => { geoRequests.union = null; });
});

// Children stay disabled until their parent is selected.
geoReset($('#district_id'), 'Select District', true);
geoReset($('#upazila_id'), 'Select Upazila', true);
geoReset($('#union_id'), 'Select Union', true);

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
                $('#quickForm select.select2-hidden-accessible').trigger('change.select2');
                geoReset($('#district_id'), 'Select District', true);
                geoReset($('#upazila_id'), 'Select Upazila', true);
                geoReset($('#union_id'), 'Select Union', true);
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