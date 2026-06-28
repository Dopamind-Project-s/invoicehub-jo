@section('title', 'تسجيل الدخول | InvoSync Jo')
<x-guest-layout>
    <section class="auth-page" dir="rtl">
        <div class="container">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="hbadge mb-3 d-inline-flex"><span class="bdot"></span>تسجيل الدخول</span>
                    <h1 class="h4 fw-bold mb-2">مرحباً بك في InvoSync Jo</h1>
                    <p class="text-muted mb-0">ادخل إلى لوحة التحكم أو تابع من نافذة الدخول السريعة في الصفحة الرئيسية.</p>
                </div>

                <x-auth-session-status class="alert alert-success" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
                    @csrf

                    <div>
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input id="email" class="form-control text-start" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus autocomplete="username">
                        <x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" />
                    </div>

                    <div>
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input id="password" class="form-control text-start" type="password" name="password" placeholder="********" required autocomplete="current-password">
                        <x-input-error :messages="$errors->get('password')" class="text-danger small mt-2" />
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <label for="remember_me" class="form-check m-0">
                            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                            <span class="form-check-label">تذكرني</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-decoration-none" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                        @endif
                    </div>

                    <button type="submit" class="bgrd btn w-100 py-3 fw-semibold">دخول</button>
                </form>
            </div>
        </div>
    </section>
</x-guest-layout>
