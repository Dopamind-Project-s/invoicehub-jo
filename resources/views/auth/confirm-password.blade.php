<x-guest-layout>
    <div class="text-center mb-4" dir="rtl">
        <h1 class="h4 fw-bold mb-2">تأكيد كلمة المرور</h1>
        <p class="text-muted mb-0">هذه منطقة آمنة. يرجى تأكيد كلمة المرور للمتابعة.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="d-grid gap-3" dir="rtl">
        @csrf

        <div>
            <label for="password" class="form-label">كلمة المرور</label>
            <input id="password" class="form-control text-start" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-2" />
        </div>

        <button type="submit" class="btn btn-primary w-100">تأكيد</button>
    </form>
</x-guest-layout>
