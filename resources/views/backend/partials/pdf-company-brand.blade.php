@php
    $pdfBusiness = $business ?? null;

    $pdfImageData = static function (?string $storedPath, ?string $fallback = null): ?string {
        foreach (array_values(array_filter([$storedPath, $fallback])) as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '' || preg_match('#^https?://#i', $candidate)) {
                continue;
            }

            $relative = preg_replace('#^/?public/#', '', str_replace('\\', '/', $candidate));
            $absolute = public_path(ltrim($relative, '/'));

            if (!is_file($absolute) || !is_readable($absolute)) {
                continue;
            }

            $mime = mime_content_type($absolute) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absolute));
        }

        return null;
    };

    $pdfLogo = $pdfImageData($pdfBusiness?->logo, 'branding/mts-logo.png');
    $pdfWatermark = $pdfImageData($pdfBusiness?->fav_icon, 'branding/mts-favicon.png');

    $pdfBusinessName = trim((string) ($pdfBusiness?->business_name ?: 'MEDI TRUST SOLUTION'));
    $pdfAddress = trim((string) ($pdfBusiness?->business_address ?: ''));
    $pdfPhone = trim((string) ($pdfBusiness?->business_phone ?: ''));
    $pdfEmail = trim((string) ($pdfBusiness?->business_email ?: ''));

    $pdfContactParts = array_values(array_filter([
        $pdfPhone !== '' ? 'Mobile: ' . $pdfPhone : null,
        $pdfEmail !== '' ? 'E-mail: ' . $pdfEmail : null,
    ]));
@endphp

<style>
    .pdf-brand-header {
        position: fixed;
        left: 0;
        right: 0;
        top: -88px;
        height: 70px;
        text-align: center;
        border-bottom: 1.5px solid #1379bf;
        padding-bottom: 7px;
    }

    .pdf-brand-logo {
        display: inline-block;
        max-width: 520px;
        max-height: 60px;
    }

    .pdf-brand-name-fallback {
        color: #e51f2a;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: .4px;
        line-height: 58px;
    }

    .pdf-brand-watermark {
        position: fixed;
        z-index: 0;
        width: 520px;
        height: 520px;

        /*
         * DomPDF can calculate percentage positions from the content box,
         * not the full A4 sheet. Use the A4 physical centre instead.
         * A4 @ 96dpi ~= 794 x 1123 px.
         */
        left: 137px;
        top: 301.5px;

        opacity: .07;
    }

    .pdf-brand-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: -66px;
        width: 100%;
    }

    .pdf-brand-footer-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pdf-brand-footer-address {
        height: 22px;
        padding: 0 12px 4px;
        vertical-align: middle;
        text-align: center;
        color: #566c80;
        font-size: 8.5px;
        font-weight: 400;
        border: 0;
    }

    .pdf-brand-footer-contact {
        height: 34px;
        padding: 0 12px;
        vertical-align: middle;
        text-align: center;
        background: #0875c9;
        color: #fff;
        border: 0;
        border-top: 2px solid #e51f2a;
        font-size: 8.5px;
        font-weight: 400;
        white-space: nowrap;
    }
</style>

<div class="pdf-brand-header">
    @if($pdfLogo)
        <img class="pdf-brand-logo" src="{{ $pdfLogo }}" alt="{{ $pdfBusinessName }}">
    @else
        <div class="pdf-brand-name-fallback">{{ $pdfBusinessName }}</div>
    @endif
</div>

@if($pdfWatermark)
    <img class="pdf-brand-watermark" src="{{ $pdfWatermark }}" alt="">
@endif

@if($pdfAddress !== '' || count($pdfContactParts))
    <div class="pdf-brand-footer">
        <table class="pdf-brand-footer-table">
            @if($pdfAddress !== '')
                <tr>
                    <td class="pdf-brand-footer-address">{{ $pdfAddress }}</td>
                </tr>
            @endif
            @if(count($pdfContactParts))
                <tr>
                    <td class="pdf-brand-footer-contact">{{ implode('   |   ', $pdfContactParts) }}</td>
                </tr>
            @endif
        </table>
    </div>
@endif
