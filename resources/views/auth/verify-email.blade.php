<x-guest-layout>
    <div class="text-center mb-4" dir="rtl">
        <h1 class="h4 fw-bold mb-2">تأكيد البريد الإلكتروني</h1>
        <p class="text-muted mb-0">قبل المتابعة، يرجى تأكيد بريدك الإلكتروني من خلال الرابط الذي أرسلناه لك.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success" dir="rtl">
            تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.
        </div>
    @endif

    <div class="d-grid gap-3" dir="rtl">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">إعادة إرسال رابط التحقق</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">تسجيل الخروج</button>
        </form>
    </div>
</x-guest-layout>
