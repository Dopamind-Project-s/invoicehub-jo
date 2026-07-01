@props(['status' => null])
@php
    $map = [
        'active' => ['نشط', 'success'], 'trialing' => ['تجريبي', 'info'], 'trial' => ['تجريبي', 'info'],
        'grace' => ['فترة سماح', 'warning'], 'expired' => ['منتهي', 'secondary'],
        'cancelled' => ['ملغي', 'danger'], 'suspended' => ['معلق', 'warning'], 'no_subscription' => ['بدون اشتراك', 'dark'],
    ];
    [$label, $tone] = $map[$status] ?? [$status ?: '—', 'secondary'];
@endphp
<span {{ $attributes->merge(['class' => "badge rounded-pill bg-{$tone}-subtle text-{$tone} border border-{$tone}-subtle px-3 py-2"]) }}>{{ $label }}</span>
