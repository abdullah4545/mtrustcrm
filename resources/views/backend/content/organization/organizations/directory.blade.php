<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Hospital / Organization Directory</title>
<style>
@page{size:A4 portrait;margin:34pt 36pt 38pt}
*{box-sizing:border-box}body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;line-height:1.65;color:#26374a;margin:0;background:white}.company{font-size:12px;font-weight:bold;color:#0b73c9;margin:0 0 4px}.report-head{border-bottom:2px solid #0b73c9;padding-bottom:12px;margin-bottom:14px}h1{font-size:21px;line-height:1.3;margin:0 0 6px;color:#15334e}.meta{font-size:9px;color:#617184}.filters{background:#f5f8fc;border-left:3px solid #0b73c9;padding:8px 11px;margin:10px 0 0;overflow-wrap:break-word;word-wrap:break-word}.organization{padding:0 0 13px;margin:0 0 13px;border-bottom:1px solid #dfe7ef;page-break-inside:auto;break-inside:auto;overflow-wrap:break-word;word-wrap:break-word}.organization h2{font-size:12px;line-height:1.5;color:#17324d;margin:0 0 5px;page-break-after:avoid;break-after:avoid}.serial{color:#0b73c9}.organization p{margin:3px 0;line-height:1.7}.profile-section{margin-top:7px}.profile-section .label{display:block;margin-bottom:2px}.visiting-card{max-width:200px;max-height:130px;margin:5px 0}.contact-block{margin-top:7px;padding-left:10px;border-left:2px solid #e0e9f2;page-break-inside:auto}.organization .metadata{font-size:8px;color:#718096}.label{font-weight:bold;color:#354e65}.muted{color:#758395}.contact{font-size:9px}.empty{padding:28px 12px;text-align:center;border:1px solid #dfe7ef;color:#617184}.toolbar{margin:0 0 20px;padding:12px;background:#f3f7fb;border:1px solid #dfe7ef}.toolbar button{padding:9px 18px;background:#0b73c9;color:white;border:0;border-radius:5px;cursor:pointer;font-weight:bold}.toolbar span{margin-left:12px;font-size:12px}.footnote{font-size:8px;color:#8592a1;margin-top:15px}
@if($isPrint)
body{max-width:850px;margin:24px auto;padding:0 20px;font-size:12px}.organization h2{font-size:15px}.meta{font-size:11px}.contact{font-size:11px}
@media print{body{max-width:none;margin:0;padding:0;font-size:10px}.toolbar{display:none!important}.organization h2{font-size:12px}.meta,.contact{font-size:9px}}
@endif
/* Flow each organization's details across the available line width in PDF and print. */
.organization p,.organization .profile-section,.organization .profile-section>div{display:inline;margin:0;line-height:1.7}
.organization p::after,.organization .profile-section::after{content:"  |  ";color:#a6b2bf}
.organization .profile-section>.label{display:inline;margin:0;font-weight:bold}
.organization .profile-section>.label::after{content:": "}
.organization .contact-block{display:block;margin:5px 0;padding:3px 0 3px 9px}
.organization .contact-block p{display:inline}
.organization .metadata{display:block;margin-top:6px}
.organization .metadata::after{content:""}
.organization .visiting-card{display:inline-block;vertical-align:middle;max-width:150px;max-height:95px;margin:3px}
</style>
</head>
<body>
@php
    $directoryText = static function ($value): string {
        $value = preg_replace('/<(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6])\s*>/i', "\n", (string)$value);
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    };
    $directoryImage = static function ($path): ?string {
        if (!$path || preg_match('#^https?://#i', $path)) return null;
        $relative = preg_replace('#^/?public/#', '', str_replace('\\', '/', $path));
        $root = realpath(public_path());
        $file = realpath(public_path(ltrim($relative, '/')));
        if (!$root || !$file || !str_starts_with($file, $root.DIRECTORY_SEPARATOR) || !is_file($file) || !is_readable($file)) return null;
        $info = @getimagesize($file);
        if (!$info || !in_array($info['mime'], ['image/jpeg','image/png','image/gif','image/webp'], true)) return null;
        return 'data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($file));
    };
@endphp
@if($isPrint)
<div class="toolbar"><button type="button" onclick="window.print()">Print / Save as PDF</button><span>{{ $organizations->count() }} matching organizations · all filtered records</span></div>
@endif
<header class="report-head">
<div class="company">{{ $business?->business_name ?? 'Medi Trust Solution' }}</div>
<h1>Hospital / Organization Directory</h1>
<div class="meta">Total records: <strong>{{ number_format($organizations->count()) }}</strong> &nbsp; | &nbsp; Generated: {{ now('Asia/Dhaka')->format('d M Y, h:i A') }}</div>
@if(count($filterLabels))<div class="filters"><strong>Selected filters:</strong> {{ implode(' · ', $filterLabels) }}</div>
@else<div class="meta">All accessible organizations</div>
@endif
</header>
@forelse($organizations as $organization)
@php
    $location = collect([$organization->union?->name, $organization->upazila?->name, $organization->district?->name, $organization->division?->name])->filter()->unique()->implode(', ');
    $phones = collect(array_merge([$organization->phone_primary, $organization->phone_secondary], $organization->phone_numbers ?? []))->filter()->unique()->implode(', ');
    $emails = collect(array_merge([$organization->email], $organization->email_addresses ?? []))->filter()->unique()->implode(', ');
    $classification = collect([$organization->category?->name, $organization->type?->name])->filter()->unique()->implode(' / ');
@endphp
<article class="organization">
<h2><span class="serial">{{ $loop->iteration }}.</span> {{ $organization->name }}</h2>
<p><span class="label">Address:</span> {{ $organization->address ?: 'Not provided' }}
@if($location) <span class="muted">({{ $location }})</span>
@endif</p>
@if($classification || $organization->no_of_beds)
<p>
@if($classification)<span class="label">Category / Type:</span> {{ $classification }}
@endif &nbsp; <span class="label">Beds:</span> {{ $organization->no_of_beds ?? 'Not provided' }}</p>
@endif
@if($phones || $emails)<p>
@if($phones)<span class="label">Phone:</span> {{ $phones }}
@endif 
@if($emails) &nbsp; <span class="label">Email:</span> {{ $emails }}
@endif</p>
@endif
<p><span class="label">Status:</span> {{ ucfirst($organization->status ?? '-') }} &nbsp; <span class="label">DGHS Facility ID:</span> {{ $organization->dghs_facility_id ?: 'Not provided' }}</p>
<p><span class="label">Division:</span> {{ $organization->division?->name ?? '-' }} · <span class="label">District:</span> {{ $organization->district?->name ?? '-' }} · <span class="label">Upazila:</span> {{ $organization->upazila?->name ?? '-' }} · <span class="label">Union:</span> {{ $organization->union?->name ?? '-' }}</p>
@if($organization->map_location_link)<p><span class="label">Map Location:</span> {{ $organization->map_location_link }}</p>
@endif
@if($organization->latitude !== null || $organization->longitude !== null)<p><span class="label">Coordinates:</span> {{ $organization->latitude ?? '-' }}, {{ $organization->longitude ?? '-' }}</p>
@endif
@if($organization->website)<p><span class="label">Website:</span> {{ $organization->website }}</p>
@endif
<div class="profile-section"><span class="label">Existing Machine</span><div>{!! e($directoryText($organization->existing_machine) ?: 'Not provided') !!}</div></div>
<div class="profile-section"><span class="label">About Organization</span><div>{!! e($directoryText($organization->about_us) ?: 'Not provided') !!}</div></div>
<div class="profile-section"><span class="label">Notes</span><div>{!! e($directoryText($organization->notes) ?: 'Not provided') !!}</div></div>
<div class="profile-section"><span class="label">Contact Persons ({{ $organization->contacts->count() }})</span></div>
@forelse($organization->contacts as $contact)
@php
    $contactPhones = collect(array_merge([$contact->phone, $contact->phone_two], $contact->phone_numbers ?? []))->filter()->unique()->implode(', ');
    $contactEmails = collect(array_merge([$contact->email], $contact->email_addresses ?? []))->filter()->unique()->implode(', ');
    $cardImage = $directoryImage($contact->image_url);
@endphp
<div class="contact-block">
<p class="contact"><span class="label">{{ $loop->iteration }}. {{ $contact->title }} {{ $contact->name ?: trim(($contact->first_name ?? '').' '.($contact->last_name ?? '')) ?: 'Name not provided' }}</span>
@if($contact->is_primary) · Primary Contact
@endif</p>
<p class="contact"><span class="label">Department:</span> {{ $contact->department?->title ?? '-' }} · <span class="label">Designation:</span> {{ $contact->designation?->title ?? '-' }} · <span class="label">Status:</span> {{ ucfirst($contact->status ?? '-') }}</p>
<p class="contact"><span class="label">Phone:</span> {{ $contactPhones ?: 'Not provided' }} · <span class="label">Email:</span> {{ $contactEmails ?: 'Not provided' }}</p>
<p class="contact"><span class="label">Address:</span> {{ $contact->address ?: 'Not provided' }}</p>
@if($contact->additional_info)<p class="contact"><span class="label">Additional Info:</span> {!! e($directoryText($contact->additional_info)) !!}</p>
@endif
@if($cardImage)<p class="contact"><span class="label">Visiting Card:</span> <img class="visiting-card" src="{{ $cardImage }}" alt="Visiting card"></p>
@elseif($contact->image_url)<p class="contact"><span class="label">Visiting Card:</span> {{ $contact->image_url }}</p>
@endif
</div>
@empty<p class="contact muted">No contact persons recorded.</p>
@endforelse
<p class="metadata">Reference: #{{ $organization->id }} · Created By: {{ $organization->creator?->name ?? '-' }} · Created: {{ $organization->created_at?->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?? '-' }} · Updated: {{ $organization->updated_at?->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?? '-' }}</p>
</article>
@empty
<div class="empty">No organizations match the selected filters.</div>
@endforelse
<div class="footnote">End of directory · {{ number_format($organizations->count()) }} records</div>
@if($isPrint)
<script>window.addEventListener('load',function(){window.print();});</script>
@endif
</body>
</html>
