@php
    $selectedOrganizationId = old('organization_id', $sale->organization_id ?? $lead->organization_id ?? $quotation->organization_id ?? null);
    $selectedOrganization = $selectedOrganizationId ? \App\Support\CrmAccess::applyOrganizationScope(\App\Models\Organization::query())->find($selectedOrganizationId) : null;
@endphp
<div class="{{ $organizationColumn ?? 'col-md-3' }}"><label class="form-label" for="sale_organization_id">Organization (Optional)</label>
<select class="form-control no-select2" name="organization_id" id="sale_organization_id">
<option value="">Select Organization</option>
@if($selectedOrganization)<option value="{{ $selectedOrganization->id }}" selected>{{ $selectedOrganization->name }}</option>@endif
</select>@error('organization_id')<div class="text-danger">{{ $message }}</div>@enderror</div>
@push('scripts')
<script>
$(function(){
    $('#sale_organization_id').select2({width:'100%',placeholder:'Search organization',allowClear:true,ajax:{url:@json(route('sales.organizations.options')),dataType:'json',delay:250,data:p=>({q:p.term||'',page:p.page||1}),processResults:r=>r}})
    .on('change',function(){ $('input[name="organization_contact_id"]').val(''); })
    .on('select2:select',function(e){
        const org=e.params.data;
        $('input[name="client_name"]').val(org.text || '');
        $('input[name="client_phone"]').val(org.client_phone || '');
        $('input[name="client_email"]').val(org.client_email || '');
    });
});
</script>
@endpush
