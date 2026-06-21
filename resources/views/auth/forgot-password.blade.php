<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-2">استعادة كلمة المرور</h1>
        <p class="text-muted mb-0">أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين.</p>
    </div>

    <x-auth-session-status class="alert alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="d-grid gap-3" dir="rtl">
        @csrf

        <div>
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" class="form-control text-start" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" />
        </div>

        <button type="submit" class="btn btn-primary w-100">إرسال رابط الاستعادة</button>
        <a class="btn btn-link text-decoration-none" href="{{ route('login') }}">العودة إلى تسجيل الدخول</a>
    </form>
</x-guest-layout>
