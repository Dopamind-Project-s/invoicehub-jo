@props(['title', 'subtitle' => null, 'actions' => null])

<div class="page-header">
    <div>
        <h1>{{ $title }}</h1>
        @if($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="page-header-actions">{{ $actions }}</div>
    @endif
</div>
