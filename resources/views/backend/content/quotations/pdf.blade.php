<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $q->quotation_no }}</title>
<style>
@page{size:A4;margin:102px 34px 105px 34px}
*{font-family:DejaVu Sans,sans-serif;box-sizing:border-box}
body{font-size:10.5px;color:#111;margin:0;line-height:1.45}
.page-break{page-break-before:always}.no-break{page-break-inside:avoid}.right{text-align:right}.center{text-align:center}.muted{color:#555}
.meta{width:100%;border-collapse:collapse;margin-bottom:24px}.meta td{border:0;padding:0;vertical-align:top}
.to-box{margin:28px 0 54px}.subject{font-size:11.5px;font-weight:700;margin:0 0 38px}.letter{font-size:11px;text-align:justify;line-height:1.65}.regards{margin-top:34px;font-weight:700;line-height:2.2}
.fin-head{width:100%;border-collapse:collapse;margin:10px 0 8px}.fin-head td{background:#d9d9d9;border:1px solid #777;text-align:center;font-weight:700;padding:5px}.fin-title{font-size:13px}
.items{width:100%;border-collapse:collapse;table-layout:fixed}.items thead{display:table-header-group}.items tr{page-break-inside:avoid}.items th,.items td{border:1px solid #555;padding:5px;vertical-align:top}.items th{text-align:center;font-weight:700}.items .sn{width:5%}.items .desc{width:51%}.items .model{width:10%}.items .qty{width:8%}.items .unitp{width:13%}.items .totalp{width:13%}.product-name{font-size:11px;font-weight:700;margin-bottom:3px}.product-desc{font-size:9.4px;line-height:1.42;text-align:justify}.product-image{display:block;max-width:150px;max-height:145px;margin:8px auto;object-fit:contain}.spec{font-size:9.2px;line-height:1.4;margin-top:5px}.money{text-align:right;font-weight:700;white-space:nowrap}.summary{width:100%;border-collapse:collapse;margin-top:-1px}.summary td{border:1px solid #555;padding:4px 6px}.summary .label{text-align:right;font-weight:700}.summary .amount{width:20%;text-align:right;font-weight:700}.terms{margin-top:32px}.terms-title{text-align:center;text-decoration:underline;font-weight:700;font-size:14px;margin-bottom:18px}.terms-body{font-size:10.5px;line-height:1.7}.terms-body p{margin:0 0 7px}.terms-body ul{margin:0;padding-left:22px}.final-note{margin-top:18px}.small{font-size:9px}
</style>
</head>
<body>
@php
    $money = fn($v) => number_format((float)$v, 2);
    $businessName = $business?->business_name ?: 'Medi Trust Solution';
    $subject = trim((string)($q->subject ?: $q->description ?: 'Supply of Medical Equipment'));
    $salutation = trim((string)($q->salutation ?: 'Dear Sir,'));
    $signOff = trim((string)($q->sign_off ?: 'Best Regards'));
    $termsTitle = trim((string)($q->terms_title ?: 'TERMS AND CONDITIONS'));
    $imgData = static function (?string $storedPath): ?string {
        if(!$storedPath || preg_match('#^https?://#i',$storedPath)) return null;
        $relative = preg_replace('#^/?public/#','',str_replace('\\','/',$storedPath));
        $absolute = public_path(ltrim($relative,'/'));
        if(!is_file($absolute) || !is_readable($absolute)) return null;
        $mime = mime_content_type($absolute) ?: 'image/jpeg';
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absolute));
    };
@endphp
@include('backend.partials.pdf-company-brand', ['business' => $business ?? null, 'showAddress' => false])

{{-- PAGE 1: Cover letter pattern from the supplied quotation --}}
<table class="meta">
<tr>
<td><b>Date:</b> {{ optional($q->issue_date)->format('d F Y') }}<br><b>Quotation No.</b> {{ $q->quotation_no }}</td>
</tr>
</table>

<div class="to-box">
    <div>To</div>
    <b>{{ $q->client_name ?: 'Concerned Authority' }}</b>
    @if($q->client_address)<div>{{ $q->client_address }}</div>@endif
    @if($q->client_phone)<div>{{ $q->client_phone }}</div>@endif
</div>

<div class="subject">Subject: Quotation of {{ $subject }}</div>
<div class="letter">
    <p>{{ $salutation }}</p>
    @if($q->cover_letter)
        {!! $q->cover_letter !!}
    @else
        <p>Thank you for taking an interest in our products and services. We are pleased to submit our quotation for your kind consideration. The detailed financial quotation, product description, product image, prices and applicable terms &amp; conditions are enclosed on the following pages.</p>
        <p>We will consider ourselves fortunate if we are successful in establishing a valued business relationship with your organization.</p>
    @endif
    @if($q->note_for_recipient)<div>{!! $q->note_for_recipient !!}</div>@endif
</div>
<div class="regards">{{ $signOff }}<br><br>{{ $businessName }}.</div>

<div class="page-break"></div>

{{-- PAGE 2+: Financial quotation with product image + description --}}
<table class="meta">
<tr>
<td><b>Date:</b> {{ optional($q->issue_date)->format('d F Y') }}<br><b>Quotation No.</b> {{ $q->quotation_no }}</td>
<td class="right">@if($q->valid_until)<b>Valid Until:</b> {{ optional($q->valid_until)->format('d F Y') }}@endif</td>
</tr>
</table>

<table class="fin-head"><tr><td>
    Financial Quotation No: {{ $q->quotation_no }} - Dated {{ optional($q->issue_date)->format('d.m.Y') }}<br>
    <span class="fin-title">Subject: {{ $subject }}</span>
</td></tr></table>

<table class="items">
<thead><tr>
<th class="sn">SN</th><th class="desc">Description</th><th class="model">Model</th><th class="qty">Qty.</th><th class="unitp">Unit Price<br><span class="small">{{ $q->tax_enabled ? 'Before Tax' : 'Excluding VAT & TAX' }}</span></th><th class="totalp">Total Price<br><span class="small">{{ $q->tax_enabled ? 'Before Tax' : 'Excluding VAT & TAX' }}</span></th>
</tr></thead>
<tbody>
@foreach($q->items as $i => $it)
@php
    $product = $it->product;
    $productImage = $imgData($product?->image_url);
    $model = $product?->sku ?: '-';
    $desc = $it->description ?: $product?->description ?: $product?->configuration_description;
@endphp
<tr>
<td class="center">{{ $i+1 }}.</td>
<td>
    <div class="product-name">{{ $it->item_name }}</div>
    @if($desc)<div class="product-desc">{!! $desc !!}</div>@endif
    @if($productImage)<img class="product-image" src="{{ $productImage }}" alt="">@endif
    @if($product)
    <div class="spec">
        @if($product->configuration_description && $product->configuration_description !== $desc)<b>Configuration:</b> {!! $product->configuration_description !!}<br>@endif
        @if($product->warranty_months)<b>Warranty:</b> {{ $product->warranty_months }} month(s)<br>@endif
        @if($product->warranty_terms_details)<b>Warranty Terms:</b> {!! $product->warranty_terms_details !!}@endif
    </div>
    @endif
</td>
<td class="center"><b>{{ $model }}</b></td>
<td class="center"><b>{{ rtrim(rtrim(number_format((float)$it->qty,2,'.',''),'0'),'.') }}</b><br>{{ $it->unit }}</td>
<td class="money">{{ $money($it->unit_price) }}</td>
<td class="money">{{ $money((float)$it->qty * (float)$it->unit_price) }}</td>
</tr>
@endforeach
</tbody>
</table>

<table class="summary">
<tr><td class="label">Sub Total</td><td class="amount">{{ $money($q->sub_total) }}</td></tr>
@if((float)$q->discount_amount > 0)<tr><td class="label">Special Discount</td><td class="amount">{{ $money($q->discount_amount) }}</td></tr>@endif
@if((float)$q->tax_amount > 0)<tr><td class="label">VAT / TAX</td><td class="amount">{{ $money($q->tax_amount) }}</td></tr>@endif
<tr><td class="label">Net Amount</td><td class="amount">{{ $money($q->grand_total) }} {{ $q->currency }}</td></tr>
</table>

<div class="terms no-break">
    <div class="terms-title">{{ $termsTitle }}</div>
    <div class="terms-body">
        @if($q->terms)
            {!! $q->terms !!}
        @else
            <p>Payment : As mutually agreed.</p>
            <p>Delivery : As per confirmed order and stock availability.</p>
            <p>Installation : As applicable for the quoted product.</p>
            <p>After Sales : Service support will be provided according to the applicable warranty terms.</p>
            @if($q->valid_until)<p>Validity : Up to {{ optional($q->valid_until)->format('d F Y') }}.</p>@endif
        @endif
    </div>
    <div class="final-note">
        @if($q->closing_note) {!! $q->closing_note !!} @else We thank you and assure you of our best attention at all times. @endif
    </div>
    <div class="regards">{{ $signOff }}<br><br>{{ $businessName }}.</div>
</div>
</body>
</html>
