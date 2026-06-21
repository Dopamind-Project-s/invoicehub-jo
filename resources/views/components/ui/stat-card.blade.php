@props(['label', 'value', 'icon' => '📊', 'tone' => 'primary'])

<div class="stat-card stat-card-{{ $tone }}">
    <div class="stat-icon">{{ $icon }}</div>
    <div>
        <span>{{ $label }}</span>
        <strong>{{ $value }}</strong>
    </div>
</div>
