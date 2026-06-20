<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-2">تعيين كلمة مرور جديدة</h1>
        <p class="text-muted mb-0">أدخل كلمة المرور الجديدة لإكمال عملية الاستعادة.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="d-grid gap-3" dir="rtl">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" class="form-control text-start" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" />
        </div>

        <div>
            <label for="password" class="form-label">كلمة المرور الجديدة</label>
            <input id="password" class="form-control text-start" type="password" name="password" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
            <input id="password_confirmation" class="form-control text-start" type="password" name="password_confirmation" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger small mt-2" />
        </div>

        <button type="submit" class="btn btn-primary w-100">حفظ كلمة المرور</button>
    </form>
</x-guest-layout>
