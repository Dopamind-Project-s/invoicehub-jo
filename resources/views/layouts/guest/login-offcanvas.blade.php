<div class="offcanvas offcanvas-end" tabindex="-1" id="lofc">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2"><div class="logo-i" style="width:30px;height:30px;font-size:.8rem"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div><h5 class="offcanvas-title mb-0">InvoSync Jo</h5></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" style="filter:invert(1)"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div class="mb-4"><span class="hbadge mb-3 d-inline-flex"><span class="bdot"></span>تسجيل الدخول</span><h3 class="mb-2">ادخل إلى لوحة تحكم InvoSync Jo</h3><p class="mb-0" style="color:var(--tx3);font-size:.9rem">استخدم بريدك وكلمة المرور للوصول إلى مساحة العمل.</p></div>
        <x-auth-session-status class="alert alert-success" :status="session('status')" />
        <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
            @csrf
            <div><label class="olbl" for="landingLoginEmail"><i class="fa-regular fa-envelope me-1"></i>البريد الإلكتروني</label><input class="oinp" type="email" id="landingLoginEmail" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="username"><x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" /></div>
            <div><label class="olbl" for="landingLoginPassword"><i class="fa-solid fa-lock me-1"></i>كلمة المرور</label><input class="oinp" type="password" id="landingLoginPassword" name="password" placeholder="********" required autocomplete="current-password"><x-input-error :messages="$errors->get('password')" class="text-danger small mt-2" /></div>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2"><label class="form-check m-0"><input class="form-check-input" type="checkbox" name="remember"><span class="form-check-label">تذكرني</span></label>@if (Route::has('password.request'))<a href="{{ route('password.request') }}" style="font-size:.8rem;color:var(--pur)">نسيت كلمة المرور؟</a>@endif</div>
            <button type="submit" class="bgrd btn w-100 py-3 fw-semibold fs-6">دخول <i class="fa-solid fa-arrow-left ms-1 fa-sm"></i></button>
        </form>
    </div>
</div>
