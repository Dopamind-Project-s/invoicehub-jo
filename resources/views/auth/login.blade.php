<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-2">تسجيل الدخول</h1>
        <p class="text-muted mb-0">مرحباً بك في منصة InvoSync Jo</p>
    </div>

    <x-auth-session-status class="alert alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="d-grid gap-3" dir="rtl">
        @csrf

        <div>
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" class="form-control text-start" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" />
        </div>

        <div>
            <label for="password" class="form-label">كلمة المرور</label>
            <input id="password" class="form-control text-start" type="password" name="password" required autocomplete="current-password">
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

        <button type="submit" class="btn btn-primary w-100">دخول</button>
    </form>
</x-guest-layout>
