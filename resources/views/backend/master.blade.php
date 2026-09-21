<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Medi Trust Solution CRM')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- link include --}}
    @include('backend.partials.links.css')

    @stack('subcss')
    <style>
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="shortcut icon" type="image/x-icon" href="{{ $business?->fav_icon ? asset($business->fav_icon) : asset('public/branding/mts-logo.png') }}">
    @php
        $bizName   = $business?->business_name ?? 'Medi Trust Solution';
        $bizTitle  = $business?->meta_title ?: ($business?->title ?: $bizName);
        $bizDesc   = $business?->meta_description ?: 'Welcome to '.$bizName;
        $bizKeys   = $business?->meta_keywords ?: '';
        $bizEmail  = $business?->business_email ?? '';
        $bizPhone  = $business?->business_phone ?? '';

        // ✅ meta image: meta_image > logo > fallback
        $metaImagePath = $business?->meta_image ?: ($business?->logo ?: null);
        $metaImageUrl  = $metaImagePath ? asset($metaImagePath) : asset('public/default-og.webp');

        $canonicalUrl  = url()->current();
    @endphp

    <meta name="description" content="{{ $bizDesc }}">
    <meta name="keywords" content="{{ $bizKeys }}">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="{{ $bizTitle }}">
    <meta itemprop="description" content="{{ $bizDesc }}">
    <meta itemprop="image" content="{{ $metaImageUrl }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $bizTitle }}">
    <meta property="og:description" content="{{ $bizDesc }}">
    <meta property="og:image" content="{{ $metaImageUrl }}">
    <meta property="og:image:secure_url" content="{{ $metaImageUrl }}">
    <meta property="og:site_name" content="{{ $bizName }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $bizTitle }}">
    <meta name="twitter:description" content="{{ $bizDesc }}">
    <meta name="twitter:image" content="{{ $metaImageUrl }}">
    <meta name="twitter:url" content="{{ $canonicalUrl }}">



    <style>
        :root{
            --mts-blue:#0b73c9;--mts-blue-dark:#075b9f;--mts-red:#ef1b2d;--mts-red-dark:#c91224;
            --mts-gold:#f4a51c;--mts-ink:#17324d;--mts-muted:#718096;--mts-bg:#f5f8fc;--mts-border:#e6edf5;
        }
        body{background:var(--mts-bg);color:var(--mts-ink)}
        a{color:var(--mts-blue)}
        .btn-primary{background:var(--mts-blue)!important;border-color:var(--mts-blue)!important;box-shadow:0 6px 16px rgba(11,115,201,.13)}
        .btn-primary:hover,.btn-primary:focus{background:var(--mts-blue-dark)!important;border-color:var(--mts-blue-dark)!important}
        .btn-danger{background:var(--mts-red)!important;border-color:var(--mts-red)!important}
        .text-primary{color:var(--mts-blue)!important}.text-danger{color:var(--mts-red)!important}
        .bg-primary{background:var(--mts-blue)!important}.bg-danger{background:var(--mts-red)!important}
        .card{border-color:var(--mts-border);box-shadow:0 8px 24px rgba(20,50,80,.045)}
        .page-header{background:transparent;border-bottom:0}
        .page-header-title h4,.page-header-title h5{color:var(--mts-ink);font-weight:750;letter-spacing:-.25px}
        .form-control:focus,.form-select:focus{border-color:rgba(11,115,201,.5)!important;box-shadow:0 0 0 .18rem rgba(11,115,201,.10)!important}
        .nxl-navigation{background:#fff;border-right:1px solid var(--mts-border)}
        .nxl-navigation .m-header{background:#fff;border-bottom:1px solid #eef3f8;min-height:72px;padding:10px 14px}
        .nxl-navigation .m-header .b-brand{display:flex;align-items:center;justify-content:center;height:52px}
        .nxl-navigation .m-header .logo{max-height:52px!important;width:auto!important;max-width:100%;object-fit:contain}
        .nxl-navigation .nxl-navbar .nxl-item .nxl-link{border-radius:10px;margin:2px 10px;color:#52677c}
        .nxl-navigation .nxl-navbar .nxl-item .nxl-link:hover{background:#f0f7fd;color:var(--mts-blue)}
        .nxl-navigation .nxl-navbar .nxl-item.active>.nxl-link,.nxl-navigation .nxl-navbar .nxl-item>.nxl-link.active{background:linear-gradient(90deg,#edf7ff,#fff4f5);color:var(--mts-blue);font-weight:700}
        .nxl-navigation .nxl-caption label{color:#9aa8b5;letter-spacing:.08em;font-size:10px}
        .nxl-header{background:rgba(255,255,255,.96)!important;border-bottom:1px solid var(--mts-border);backdrop-filter:blur(12px)}
        .nxl-header .nxl-head-link:hover{color:var(--mts-blue)}
        .footer{border-top:1px solid var(--mts-border);background:#fff}
        .table thead th{background:#f7faff;color:#496078;border-color:var(--mts-border);font-weight:700}
        .dropdown-menu{border-color:var(--mts-border)!important;box-shadow:0 16px 40px rgba(20,50,80,.10)!important}
        .badge.bg-soft-primary,.bg-soft-primary{background:#eaf5ff!important}.text-primary{color:var(--mts-blue)!important}
        .mts-brand-rule{height:3px;border-radius:99px;background:linear-gradient(90deg,var(--mts-blue) 0 55%,var(--mts-red) 55% 88%,var(--mts-gold) 88%);}
        @media(max-width:767.98px){.nxl-navigation .m-header{min-height:64px}.nxl-navigation .m-header .b-brand{height:44px}.nxl-navigation .m-header .logo{max-height:44px!important}}
    </style>

</head>

<body>

    @include('backend.partials.sidebar')

    @include('backend.partials.header')

    <main class="nxl-container">
        <div class="nxl-content">

            @yield('maincontent')

        </div>

        @include('backend.partials.footer')

    </main>


    {{-- js link includes --}}
    @include('backend.partials.links.js')

    @stack('scripts')

    @stack('modals')
    <style>
        /* Keep Select2 text properly inside and vertically centered in the control. */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            min-height: 42px !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            border: 1px solid #ced4da !important;
            border-radius: .375rem !important;
            background-color: #fff !important;
            position: relative !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            width: 100% !important;
            line-height: normal !important;
            padding-left: 12px !important;
            padding-right: 38px !important;
            margin: 0 !important;
            display: block !important;
            color: #283c50;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            line-height: normal !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            top: 0 !important;
            right: 6px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute !important;
            right: 28px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            line-height: 1 !important;
            z-index: 2;
        }

        /* Global modal safety: blur only the backdrop, never modal content. */
        .modal-backdrop {
            background: rgba(15, 23, 42, .42) !important;
            backdrop-filter: blur(3px) !important;
            -webkit-backdrop-filter: blur(3px) !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        .modal {
            filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        .modal.show {
            pointer-events: auto !important;
        }
        .modal .modal-dialog,
        .modal .modal-content {
            position: relative;
            filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            opacity: 1 !important;
        }
        .modal .modal-content {
            background: #fff !important;
        }
        body.modal-open { overflow: hidden !important; touch-action: none; }
        .modal { overflow-x: hidden; overflow-y: auto !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
        .modal-dialog-scrollable { max-height: calc(100dvh - 1rem); }
        .modal-dialog-scrollable .modal-content { max-height: calc(100dvh - 1rem); }
        .modal-dialog-scrollable .modal-body { overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
        .select2-container--open { z-index: 2000 !important; }
        .select2-dropdown { max-width: calc(100vw - 20px); }
        @media (max-width: 767.98px) {
            .modal-dialog { margin: .35rem; min-height: calc(100dvh - .7rem); }
            .modal-dialog-centered { min-height: calc(100dvh - .7rem); }
            .modal-content { max-height: calc(100dvh - .7rem); }
            .modal-body { overflow-y: auto !important; -webkit-overflow-scrolling: touch; }
            .select2-container .select2-selection--single { min-height: 44px; }
        }
    </style>
    <script>
        document.addEventListener('wheel', function (event) {
            if (event.target instanceof HTMLInputElement && event.target.type === 'number') {
                event.target.blur();
            }
        }, { passive: true, capture: true });

        document.addEventListener('keydown', function (event) {
            if (event.target instanceof HTMLInputElement && event.target.type === 'number' && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
                event.preventDefault();
            }
        });

        // Keep every Bootstrap modal at body level so theme transforms/filters cannot blur or block it.
        document.addEventListener('show.bs.modal', function (event) {
            const modal = event.target;
            if (!(modal instanceof HTMLElement) || !modal.classList.contains('modal')) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            const openModals = document.querySelectorAll('.modal.show').length;
            const modalZ = 1060 + (openModals * 20);
            modal.style.setProperty('z-index', modalZ, 'important');
            modal.style.setProperty('filter', 'none', 'important');
            modal.style.setProperty('backdrop-filter', 'none', 'important');
            modal.style.setProperty('-webkit-backdrop-filter', 'none', 'important');

            const dialog = modal.querySelector('.modal-dialog');
            const content = modal.querySelector('.modal-content');
            [dialog, content].forEach(function (el) {
                if (!el) return;
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('backdrop-filter', 'none', 'important');
                el.style.setProperty('-webkit-backdrop-filter', 'none', 'important');
                el.style.setProperty('opacity', '1', 'important');
            });

            window.setTimeout(function () {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const backdrop = backdrops[backdrops.length - 1];
                if (backdrop) {
                    backdrop.style.setProperty('z-index', modalZ - 10, 'important');
                    backdrop.style.setProperty('pointer-events', 'auto', 'important');
                }
            }, 0);
        });

        document.addEventListener('hidden.bs.modal', function () {
            const visibleModals = document.querySelectorAll('.modal.show');
            if (visibleModals.length > 0) {
                document.body.classList.add('modal-open');
            } else {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            }
        });
    </script>

</body>

</html>
