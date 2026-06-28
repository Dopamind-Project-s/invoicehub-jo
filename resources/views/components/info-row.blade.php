@props(['label', 'value' => null])
<div {{ $attributes->merge(['class' => 'd-flex justify-content-between gap-3 py-2 border-bottom border-light-subtle']) }}>
    <span class="text-muted">{{ $label }}</span>
    <strong class="text-dark text-end">{{ $value ?? $slot ?? '—' }}</strong>
</div>
