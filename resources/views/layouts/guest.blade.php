<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $business?->business_name ?? 'Medi Trust Solution' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#f5f8fc!important}.mts-guest-card{border:1px solid #e5edf6;box-shadow:0 18px 50px rgba(20,50,80,.08)!important}.mts-guest-logo{max-width:360px;width:88%;height:auto}.mts-guest-rule{height:3px;background:linear-gradient(90deg,#0b73c9 0 56%,#ef1b2d 56% 88%,#f4a51c 88%);border-radius:99px}</style>
</head>
<body class="font-sans text-gray-900 antialiased">
<div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6">
    <a href="/" class="flex justify-center mb-6"><img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" class="mts-guest-logo" alt="Medi Trust Solution"></a>
    <div class="w-full sm:max-w-md bg-white overflow-hidden sm:rounded-2xl mts-guest-card"><div class="mts-guest-rule"></div><div class="px-6 py-6 sm:px-8 sm:py-8">{{ $slot }}</div></div>
    <div class="text-xs text-gray-400 mt-5">{{ $business?->business_name ?? 'Medi Trust Solution' }} · Secure CRM</div>
</div>
</body>
</html>
