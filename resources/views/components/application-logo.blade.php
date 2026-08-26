<img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" alt="{{ $business?->business_name ?? 'Medi Trust Solution' }}" {{ $attributes->merge(['style' => 'object-fit:contain']) }}>
