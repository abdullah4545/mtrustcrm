<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Medi Trust Solution">
    <title>{{ $business?->business_name ?? 'Medi Trust Solution' }} | Sign In</title>
    <link rel="stylesheet" href="{{ asset('public/backend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/backend/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/backend/css/theme.min.css') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset($business?->fav_icon ?: 'public/branding/mts-logo.png') }}">
    <style>
        :root{--blue:#0b73c9;--red:#ef1b2d;--gold:#f4a51c;--ink:#17324d;--muted:#738196;--border:#e4edf6}
        *{box-sizing:border-box}body{margin:0;background:#f5f8fc;color:var(--ink);font-family:Inter,Arial,sans-serif}
        .mts-auth{min-height:100vh;display:grid;grid-template-columns:minmax(0,1.08fr) minmax(420px,.92fr)}
        .mts-auth-brand{position:relative;overflow:hidden;padding:56px;display:flex;align-items:center;background:linear-gradient(145deg,#fafdff 0%,#eef7ff 58%,#fff5f6 100%)}
        .mts-auth-brand:before,.mts-auth-brand:after{content:"";position:absolute;border-radius:50%;filter:blur(2px)}
        .mts-auth-brand:before{width:390px;height:390px;background:rgba(11,115,201,.07);right:-120px;top:-120px}.mts-auth-brand:after{width:290px;height:290px;background:rgba(239,27,45,.055);left:-100px;bottom:-100px}
        .brand-inner{position:relative;z-index:1;max-width:720px}.brand-logo{width:min(100%,620px);height:auto;display:block;margin-bottom:48px}.eyebrow{font-size:12px;text-transform:uppercase;letter-spacing:.18em;color:var(--blue);font-weight:800}.brand-title{font-size:44px;line-height:1.08;letter-spacing:-1.2px;font-weight:800;margin:12px 0 16px}.brand-copy{max-width:560px;color:#61758a;font-size:15px;line-height:1.75}.brand-rule{width:160px;height:4px;border-radius:99px;background:linear-gradient(90deg,var(--blue) 0 56%,var(--red) 56% 88%,var(--gold) 88%);margin:26px 0}.brand-contact{font-size:12px;color:#6c7d8e;line-height:1.8}
        .mts-auth-form{display:flex;align-items:center;justify-content:center;background:#fff;padding:40px}.login-card{width:100%;max-width:430px}.login-logo-mobile{display:none;width:100%;max-width:330px;margin:0 auto 32px}.login-kicker{color:var(--blue);font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:.12em}.login-title{font-size:30px;font-weight:800;letter-spacing:-.7px;margin:8px 0}.login-sub{color:var(--muted);font-size:13px;margin-bottom:30px}.form-label{font-size:12px;font-weight:700;color:#40566d}.form-control{min-height:50px;border-radius:12px;border:1px solid var(--border);font-size:14px;padding:11px 14px}.form-control:focus{border-color:rgba(11,115,201,.55);box-shadow:0 0 0 .2rem rgba(11,115,201,.09)}.password-wrap{position:relative}.password-wrap .form-control{padding-right:46px}.password-toggle{position:absolute;right:13px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#8292a2;padding:6px}.login-btn{min-height:50px;border:0;border-radius:12px;background:linear-gradient(135deg,var(--blue),#0867b2);box-shadow:0 10px 24px rgba(11,115,201,.18);font-weight:750}.login-btn:hover{background:#075b9f}.login-footer{border-top:1px solid #edf2f7;margin-top:30px;padding-top:18px;color:#92a0ad;font-size:11px;text-align:center}.text-brand{color:var(--blue)!important}.alert{border-radius:12px}
        @media(max-width:991.98px){.mts-auth{display:block}.mts-auth-brand{display:none}.mts-auth-form{min-height:100vh;padding:26px 18px}.login-logo-mobile{display:block}.login-card{max-width:460px}.login-title{font-size:26px}}
    </style>
</head>
<body>
<main class="mts-auth">
    <section class="mts-auth-brand">
        <div class="brand-inner">
            <img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" class="brand-logo" alt="Medi Trust Solution">
            <div class="eyebrow">Enterprise CRM Platform</div>
            <h1 class="brand-title">Connected teams.<br>Better field execution.</h1>
            <div class="brand-rule"></div>
            <p class="brand-copy">A secure workspace for field activity, leads, organizations, quotations, sales, follow-ups, reports and business operations.</p>
            <div class="brand-contact">
                {{ $business?->business_address ?? 'House # 1148, Avenue # 10, Road # 9/A, Mirpur DOHS, Mirpur, Dhaka-1216, Bangladesh' }}<br>
                {{ $business?->business_phone ?? '+88 01711-924911, 01711-220161, 01711-994343' }} &nbsp;·&nbsp; {{ $business?->business_email ?? 'meditrustsolution@gmail.com' }}
            </div>
        </div>
    </section>
    <section class="mts-auth-form">
        <div class="login-card">
            <img src="{{ asset($business?->logo ?: 'public/branding/mts-logo.png') }}" class="login-logo-mobile" alt="Medi Trust Solution">
            <div class="login-kicker">Authorized Access</div>
            <h2 class="login-title">Welcome back</h2>
            <p class="login-sub">Sign in with your company account to continue.</p>

            @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@company.com" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrap">
                        <input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password"><i class="feather-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <label class="d-flex align-items-center gap-2 mb-0 fs-12 text-muted"><input type="checkbox" name="remember" value="1" class="form-check-input mt-0"> Remember me</label>
                    @if(Route::has('password.request'))<a href="{{ route('password.request') }}" class="fs-12 text-brand fw-semibold">Forgot password?</a>@endif
                </div>
                <button type="submit" class="btn btn-primary login-btn w-100">Sign In</button>
            </form>
            <div class="login-footer">{{ $business?->business_name ?? 'Medi Trust Solution' }} · Secure CRM Access</div>
        </div>
    </section>
</main>
<script src="{{ asset('public/backend/vendors/js/vendors.min.js') }}"></script>
<script>
const pwd=document.getElementById('password'),btn=document.getElementById('togglePassword');btn?.addEventListener('click',()=>{const show=pwd.type==='password';pwd.type=show?'text':'password';btn.innerHTML=show?'<i class="feather-eye-off"></i>':'<i class="feather-eye"></i>'});
</script>
</body>
</html>
