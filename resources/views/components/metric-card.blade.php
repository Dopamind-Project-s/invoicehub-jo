@props(['label', 'value', 'icon' => '📊', 'tone' => 'primary', 'hint' => null])
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100']) }}>
    <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-4 bg-{{ $tone }}-subtle text-{{ $tone }} d-inline-flex align-items-center justify-content-center fs-4" style="width:3.25rem;height:3.25rem">{{ $icon }}</div>
        <div class="min-w-0">
            <div class="text-muted small">{{ $label }}</div>
            <div class="h4 mb-0 fw-bold">{{ $value }}</div>
            @if($hint)<div class="small text-muted mt-1">{{ $hint }}</div>@endif
        </div>
    </div>
</div>
