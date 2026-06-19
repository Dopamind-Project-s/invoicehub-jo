@php
    $items = [
        ...((auth()->user()?->isSuperAdmin()) ? [
            ['label' => 'لوحة المدير', 'route' => 'admin.dashboard', 'icon' => '📊'],
            ['label' => 'إدارة الشركات', 'route' => 'admin.companies.index', 'icon' => '🏢'],
        ] : []),
        ['label' => 'الفواتير', 'route' => 'invoices.index', 'icon' => '🧾'],
        ['label' => 'الشركات', 'route' => 'companies.index', 'icon' => '🏢'],
        ['label' => 'العملاء', 'route' => 'customers.index', 'icon' => '👥'],
    ];
@endphp

<aside class="app-sidebar" id="appSidebar" aria-label="القائمة الرئيسية">
    <div class="sidebar-brand">
        <img src="{{ asset('vendor/zaha-theme/assets/logos/logo.svg') }}" alt="InvoiceHub Jo" class="sidebar-logo">
        <div>
            <strong>InvoiceHub Jo</strong>
            <span>نظام الفوترة الأردني</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        @foreach($items as $item)
            <a class="sidebar-link @if(request()->routeIs($item['route'])) active @endif" href="{{ route($item['route']) }}">
                <span class="sidebar-icon">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
