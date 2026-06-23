@props(['company' => null, 'mode' => null])
@php
    $routeCompany = $company ?: request()->route('company');
    $isAdmin = $mode === 'admin' || (! $mode && auth()->check() && auth()->user()->isSuperAdmin() && request()->is('admin*'));
    $isCompany = $mode === 'company' || (! $isAdmin && $routeCompany);
@endphp

<aside id="appSidebar" class="sidebar-original">
    <div class="sidebar-brand">
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <img class="brand-logo" src="{{ asset('assets/logos/logo2.svg') }}" alt="InvoSync Jo">
            <span class="brand-title">InvoSync Jo</span>
        </a>
    </div>

    <p class="side-comment">{{ $isAdmin ? 'إدارة المنصة' : 'مساحة المنشأة' }}</p>
    <ul class="side-list">
        @if($isAdmin)
            <li class="side-item {{ request()->routeIs('admin.dashboard*') ? 'selected' : '' }}"><a href="{{ route('admin.dashboard') }}"><span>📊</span><span>لوحة التحكم</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.companies.*') ? 'selected' : '' }}"><a href="{{ route('admin.companies.index') }}"><span>🏢</span><span>المنشآت</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.feature-keys.*') ? 'selected' : '' }}"><a href="{{ route('admin.feature-keys.index') }}"><span>🧩</span><span>مفاتيح المزايا</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.plans.*') ? 'selected' : '' }}"><a href="{{ route('admin.plans.index') }}"><span>💳</span><span>الباقات</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.landing-cms.*') ? 'selected' : '' }}"><a href="{{ route('admin.landing-cms.settings.edit') }}"><span>🌐</span><span>الموقع الإلكتروني</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.landing-cms.settings.*') ? 'selected' : '' }}"><a href="{{ route('admin.landing-cms.settings.edit') }}"><span>⚙️</span><span>الإعدادات العامة</span></a></li>
            <li class="side-item {{ request()->routeIs('admin.landing-cms.faqs.*') ? 'selected' : '' }}"><a href="{{ route('admin.landing-cms.faqs.index') }}"><span>❓</span><span>الأسئلة الشائعة</span></a></li>
            <li class="side-item"><a href="{{ route('admin.plans.index') }}"><span>🏷️</span><span>باقات الموقع</span></a></li>
            <li class="side-item"><a href="{{ route('admin.companies.index') }}"><span>👥</span><span>المستخدمون</span></a></li>
            <li class="side-item"><a href="{{ route('admin.companies.index') }}"><span>📝</span><span>سجل النشاطات</span></a></li>
        @elseif($isCompany && $routeCompany)
            <li class="side-item {{ request()->routeIs('company.dashboard') || request()->routeIs('workspace.companies.show') ? 'selected' : '' }}"><a href="{{ route('company.dashboard', $routeCompany) }}"><span>🏠</span><span>لوحة التحكم</span></a></li>
            <li class="side-item {{ request()->routeIs('company.products.*') ? 'selected' : '' }}"><a href="{{ route('company.products.index', $routeCompany) }}"><span>📦</span><span>المنتجات</span></a></li>
            <li class="side-item {{ request()->routeIs('company.product-categories.*') ? 'selected' : '' }}"><a href="{{ route('company.product-categories.index', $routeCompany) }}"><span>🗂</span><span>التصنيفات</span></a></li>
            <li class="side-item {{ request()->routeIs('company.units.*') ? 'selected' : '' }}"><a href="{{ route('company.units.index', $routeCompany) }}"><span>📏</span><span>الوحدات</span></a></li>
            <li class="side-item {{ request()->routeIs('company.tax-profiles.*') ? 'selected' : '' }}"><a href="{{ route('company.tax-profiles.index', $routeCompany) }}"><span>🧮</span><span>الضرائب</span></a></li>
            <li class="side-item {{ request()->routeIs('company.contacts.*') ? 'selected' : '' }}"><a href="{{ route('company.contacts.index', $routeCompany) }}"><span>🤝</span><span>العملاء والموردون</span></a></li>
            <li class="side-item {{ request()->routeIs('company.invoices.*') ? 'selected' : '' }}"><a href="{{ route('company.invoices.index', $routeCompany) }}"><span>🧾</span><span>الفواتير</span></a></li>
            <li class="side-item {{ request()->routeIs('company.invoice-templates.*') ? 'selected' : '' }}"><a href="{{ route('company.invoice-templates.index', $routeCompany) }}"><span>📄</span><span>قوالب الفواتير</span></a></li>
            <li class="side-item {{ request()->routeIs('company.users.*') ? 'selected' : '' }}"><a href="{{ route('company.users.index', $routeCompany) }}"><span>👥</span><span>المستخدمون</span></a></li>
            <li class="side-item {{ request()->routeIs('company.settings.*') ? 'selected' : '' }}"><a href="{{ route('company.settings.edit', $routeCompany) }}"><span>⚙️</span><span>الإعدادات</span></a></li>
            <li class="side-item {{ request()->routeIs('company.activity.*') ? 'selected' : '' }}"><a href="{{ route('company.activity.index', $routeCompany) }}"><span>📝</span><span>سجل النشاطات</span></a></li>
        @else
            <li class="side-item {{ request()->routeIs('dashboard') ? 'selected' : '' }}"><a href="{{ route('dashboard') }}"><span>🏠</span><span>لوحة التحكم</span></a></li>
        @endif
    </ul>
</aside>
