@props(['health'])
<span {{ $attributes->merge(['class' => 'badge rounded-pill px-3 py-2 '.($health['class'] ?? 'bg-secondary')]) }}>{{ $health['label'] ?? '—' }}</span>
