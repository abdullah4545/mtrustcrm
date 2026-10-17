<style>
/* Shared reference design; scoped to the four CRM lists. */
:is(#activityTable,#orgTable,#leadTable,#salesTable){border-collapse:collapse!important;width:100%!important;color:#173566!important;font-size:14px!important;background:#fff!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) thead th{background:#f5f8fb!important;color:#102a59!important;font-size:12px!important;font-weight:750!important;text-transform:uppercase;white-space:nowrap;padding:15px 18px!important;border:1px solid #dce4ed!important;vertical-align:middle!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) tbody td{padding:13px 18px!important;border:1px solid #dce4ed!important;vertical-align:middle!important;line-height:1.45!important;box-shadow:none!important;background:#fff!important;color:#173566!important;font-size:14px!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) tbody tr:hover td{background:#fafcff!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .btn{border-radius:4px!important;font-size:12px!important;font-weight:700!important;text-transform:uppercase;padding:7px 12px!important;line-height:1.25!important;margin:2px!important;box-shadow:none!important;white-space:nowrap}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .btn-primary{background:#0078e8!important;border-color:#0078e8!important;color:white!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .btn-success{background:#00bf63!important;border-color:#00bf63!important;color:white!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .btn-warning{background:#ff9c00!important;border-color:#ff9c00!important;color:white!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .btn-danger{background:#f00032!important;border-color:#f00032!important;color:white!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .badge{border-radius:4px!important;padding:8px 10px!important;line-height:1.2!important;font-size:12px!important;font-weight:650!important;white-space:normal}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .badge.bg-secondary{background:#73859e!important;color:#fff!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .badge.bg-light{background:#edf1f6!important;color:#173566!important}
:is(#activityTable,#orgTable,#leadTable,#salesTable) .form-check-input{width:18px;height:18px;cursor:pointer;border:2px solid #576675}
#activityTable{min-width:1200px!important}
#activityTable .activity-date{min-width:190px!important;white-space:nowrap}
#activityTable .activity-organization{min-width:220px!important;max-width:300px!important}
#activityTable .activity-location{min-width:310px!important;max-width:520px!important}
#activityTable .activity-money{min-width:110px!important;text-align:left!important}
#activityTable .activity-actions-cell{min-width:275px!important;width:290px!important}
#activityTable .activity-actions{gap:2px!important;min-width:255px!important;max-width:300px!important}
#activityTable .activity-actions .btn{min-height:32px!important}
.crm-created{margin-bottom:5px;font-weight:500}
#activityTable .crm-staff-badge{display:inline-flex;align-items:center;gap:7px;background:#00b75a;color:#fff;border-radius:4px;padding:6px 10px;font-size:12px;font-weight:700;white-space:normal}
.crm-routes{display:flex;flex-direction:column;gap:7px}
.crm-route{display:flex;align-items:center;gap:12px}
.crm-route-from,.crm-route-to{display:inline-block;border-radius:7px;padding:8px 13px;font-size:13px;font-weight:600;overflow-wrap:anywhere;white-space:normal}
.crm-route-from{background:#e4f1ff;color:#1756a0}
.crm-route-to{background:#e2f9e9;color:#138a48}
.crm-route-arrow{color:#7a8087;font-size:21px;flex-shrink:0}
.crm-amount{white-space:nowrap;line-height:1.45}
.crm-amount strong{font-weight:750}
:is(#orgTable,#leadTable,#salesTable) td:last-child{min-width:200px}
@media(max-width:767px){
 #activityTable{min-width:1150px!important}
 .crm-route{gap:8px}
 :is(#activityTable,#orgTable,#leadTable,#salesTable) tbody td{padding:10px 12px!important}
}
</style>
