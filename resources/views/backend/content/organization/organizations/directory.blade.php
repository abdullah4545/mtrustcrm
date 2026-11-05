<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Hospital / Organization Directory</title>
<style>
@page{size:A4 portrait;margin:34pt 36pt 38pt}
*{box-sizing:border-box}body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;line-height:1.65;color:#26374a;margin:0;background:white}.company{font-size:12px;font-weight:bold;color:#0b73c9;margin:0 0 4px}.report-head{border-bottom:2px solid #0b73c9;padding-bottom:12px;margin-bottom:14px}h1{font-size:21px;line-height:1.3;margin:0 0 6px;color:#15334e}.meta{font-size:9px;color:#617184}.filters{background:#f5f8fc;border-left:3px solid #0b73c9;padding:8px 11px;margin:10px 0 0;overflow-wrap:break-word;word-wrap:break-word}.organization{padding:0 0 13px;margin:0 0 13px;border-bottom:1px solid #dfe7ef;page-break-inside:avoid;break-inside:avoid;overflow-wrap:break-word;word-wrap:break-word}.organization h2{font-size:12px;line-height:1.5;color:#17324d;margin:0 0 5px;page-break-after:avoid;break-after:avoid}.serial{color:#0b73c9}.organization p{margin:3px 0;line-height:1.7}.label{font-weight:bold;color:#354e65}.muted{color:#758395}.contact{font-size:9px}.empty{padding:28px 12px;text-align:center;border:1px solid #dfe7ef;color:#617184}.toolbar{margin:0 0 20px;padding:12px;background:#f3f7fb;border:1px solid #dfe7ef}.toolbar button{padding:9px 18px;background:#0b73c9;color:white;border:0;border-radius:5px;cursor:pointer;font-weight:bold}.toolbar span{margin-left:12px;font-size:12px}.footnote{font-size:8px;color:#8592a1;margin-top:15px}
@if($isPrint)
body{max-width:850px;margin:24px auto;padding:0 20px;font-size:12px}.organization h2{font-size:15px}.meta{font-size:11px}.contact{font-size:11px}
@media print{body{max-width:none;margin:0;padding:0;font-size:10px}.toolbar{display:none!important}.organization h2{font-size:12px}.meta,.contact{font-size:9px}}
@endif
</style>
</head>
<body>
@if($isPrint)
<div class="toolbar"><button type="button" onclick="window.print()">Print / Save as PDF</button><span>{{ $organizations->count() }} matching organizations · all filtered records</span></div>
@endif
<header class="report-head">
<div class="company">{{ $business?->business_name ?? 'Medi Trust Solution' }}</div>
<h1>Hospital / Organization Directory</h1>
<div class="meta">Total records: <strong>{{ number_format($organizations->count()) }}</strong> &nbsp; | &nbsp; Generated: {{ now('Asia/Dhaka')->format('d M Y, h:i A') }}</div>
@if(count($filterLabels))<div class="filters"><strong>Selected filters:</strong> {{ implode(' · ', $filterLabels) }}</div>@else<div class="meta">All accessible organizations</div>@endif
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
<p><span class="label">Address:</span> {{ $organization->address ?: 'Not provided' }}@if($location) <span class="muted">({{ $location }})</span>@endif</p>
@if($classification || $organization->no_of_beds)
<p>@if($classification)<span class="label">Category / Type:</span> {{ $classification }}@endif @if($organization->no_of_beds) &nbsp; <span class="label">Beds:</span> {{ $organization->no_of_beds }}@endif</p>
@endif
@if($phones || $emails)<p>@if($phones)<span class="label">Phone:</span> {{ $phones }}@endif @if($emails) &nbsp; <span class="label">Email:</span> {{ $emails }}@endif</p>@endif
@if($organization->website)<p><span class="label">Website:</span> {{ $organization->website }}</p>@endif
@foreach($organization->contacts as $contact)
@php
    $contactPhones = collect(array_merge([$contact->phone, $contact->phone_two], $contact->phone_numbers ?? []))->filter()->unique()->implode(', ');
    $contactEmails = collect(array_merge([$contact->email], $contact->email_addresses ?? []))->filter()->unique()->implode(', ');
@endphp
@if($contact->name || $contactPhones || $contactEmails)
<p class="contact"><span class="label">Contact:</span> {{ $contact->name ?: 'Name not provided' }}@if($contact->designation?->title) ({{ $contact->designation->title }})@endif @if($contactPhones) · {{ $contactPhones }}@endif @if($contactEmails) · {{ $contactEmails }}@endif</p>
@endif
@endforeach
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
