@extends('layouts.app')

@section('content')
<style>
    .template-page-header{background:linear-gradient(135deg,#f8fdff,#eef9fb);border:1px solid #d9f1f5;border-radius:22px;padding:22px 24px;margin-bottom:24px}
    .template-card{border:1px solid #e5eef4;border-radius:22px;overflow:hidden;transition:.2s ease;background:#fff;position:relative}
    .template-card:hover{transform:translateY(-4px);box-shadow:0 18px 38px rgba(15,23,42,.10)}
    .template-card.is-selected{border-color:#00a9c4;box-shadow:0 16px 36px rgba(0,169,196,.16)}
    .template-preview{height:190px;background:linear-gradient(180deg,#f8fafc,#eef6f8);padding:16px;display:flex;align-items:center;justify-content:center;border-bottom:1px solid #e5eef4}
    .mini-invoice{width:150px;height:168px;background:#fff;border-radius:10px;box-shadow:0 10px 25px rgba(15,23,42,.12);padding:12px;direction:rtl;border-top:6px solid #00a9c4}
    .mini-invoice.modern{border-top:0;background:linear-gradient(135deg,#00a9c4 0 30%,#fff 30%)}
    .mini-invoice.bilingual{direction:ltr;border-color:#12c2b2}
    .mini-invoice.receipt{width:118px;border-style:dashed;border-top-color:#334155}
    .mini-invoice.corporate{border-top-color:#1f4f75}
    .mini-line{height:6px;border-radius:999px;background:#dbe7ee;margin-bottom:7px}
    .mini-line.short{width:48%}.mini-line.mid{width:70%}.mini-line.accent{background:#00a9c4}.mini-grid{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin:12px 0}.mini-box{height:26px;border-radius:6px;background:#eef6f8}.template-meta{display:flex;gap:8px;flex-wrap:wrap}.template-meta .pill{background:#f1f9fb;color:#0f6170;border:1px solid #d7eef3;border-radius:999px;padding:4px 10px;font-size:.78rem}.selected-ribbon{position:absolute;top:14px;inset-inline-start:14px;background:#00a9c4;color:#fff;border-radius:999px;padding:5px 11px;font-size:.78rem;z-index:2}.template-actions .btn{border-radius:999px}.preview-image{max-width:100%;max-height:100%;object-fit:cover;border-radius:12px;box-shadow:0 10px 25px rgba(15,23,42,.12)}
</style>

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

    <div class="row g-4">
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
            <div class="col-md-6 col-xl-4">
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

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h2 class="h5 mb-2">{{ $template->name }}</h2>
                                <div class="template-meta">
                                    <span class="pill">{{ $template->language }}</span>
                                    <span class="pill">{{ $template->layout_type }}</span>
                                    <span class="pill">{{ $template->is_active ? 'فعال' : 'معطل' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="template-actions d-flex flex-wrap gap-2 mt-4">
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
