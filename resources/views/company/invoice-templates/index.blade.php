@extends('layouts.app')

@section('content')
<div class="template-page-header d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
    <div>
        <h1 class="h3 mb-2">قوالب الفواتير</h1>
        <p class="text-muted mb-0">اختر قالب الفواتير المناسب لمنشأتك، وعاين الشكل أو نزّل نسخة PDF تجريبية قبل الاعتماد.</p>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<form method="post" action="{{ route('company.invoice-templates.update', $company) }}">
    @csrf
    @method('PUT')

    <div class="row g-3">
        @foreach($templates as $template)
            @php
                $isSelected = (string) $selected === (string) $template->id || (! $selected && $template->is_default);
                $previewVariant = match ($template->slug) {
                    'arabic-modern' => 'modern',
                    'bilingual-ar-en' => 'bilingual',
                    'retail-receipt' => 'receipt',
                    'corporate-tax' => 'corporate',
                    default => 'classic',
                };
            @endphp
            <div class="col-lg-4 col-md-6">
                <article class="template-card h-100 {{ $isSelected ? 'is-selected' : '' }}">
                    @if($isSelected)
                        <div class="selected-ribbon">القالب الحالي</div>
                    @endif

                    <div class="template-preview">
                        @if($template->preview_image)
                            <img src="{{ asset($template->preview_image) }}" alt="{{ $template->name }}" class="preview-image" onerror="this.style.display='none';this.nextElementSibling.classList.remove('d-none')">
                        @endif
                        <div class="mini-invoice {{ $previewVariant }} {{ $template->preview_image ? 'd-none' : '' }}" aria-label="معاينة بسيطة للقالب">
                            <div class="mini-line accent short"></div>
                            <div class="mini-line mid"></div>
                            <div class="mini-line"></div>
                            <div class="mini-grid"><span class="mini-box"></span><span class="mini-box"></span></div>
                            <div class="mini-line"></div>
                            <div class="mini-line"></div>
                            <div class="mini-line short"></div>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h2 class="h6 mb-2">{{ $template->name }}</h2>
                                <div class="template-meta">
                                    <span class="pill">{{ $template->language }}</span>
                                    <span class="pill">{{ $template->layout_type }}</span>
                                    <span class="pill">{{ $template->is_active ? 'فعال' : 'معطل' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="template-actions d-flex flex-wrap gap-2 mt-3">
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('company.invoice-templates.preview', [$company, $template]) }}">معاينة</a>
                            <a class="btn btn-sm btn-outline-dark" href="{{ route('company.invoice-templates.preview', [$company, $template, 'download' => 1]) }}">تنزيل PDF</a>
                            <button class="btn btn-sm {{ $isSelected ? 'btn-primary' : 'btn-outline-success' }}" name="invoice_template_id" value="{{ $template->id }}">
                                {{ $isSelected ? 'محدد كافتراضي' : 'اختيار كقالب افتراضي' }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</form>
@endsection
