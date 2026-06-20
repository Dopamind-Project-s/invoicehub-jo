@extends('layouts.company-workspace')
@section('title', 'قوالب فواتير المنشأة')
@section('content')
<x-layout.page-header title="قوالب فواتير المنشأة" subtitle="اختر القالب الافتراضي واستعرض معاينة PDF/طباعة قبل الاستخدام." />
<form method="post" action="{{ route('company.invoice-templates.update', $company) }}" class="card card-body">@csrf @method('PUT')
<div class="row g-3">@foreach($templates as $template)<div class="col-md-4"><div class="border rounded p-3 h-100"><label class="form-check d-block"><input class="form-check-input" type="radio" name="invoice_template_id" value="{{ $template->id }}" @checked((string)$selected === (string)$template->id || (!$selected && $template->is_default))><span class="form-check-label"><strong>{{ $template->name }}</strong></span></label><dl class="small text-muted mt-2 mb-3"><dt>اللغة</dt><dd>{{ $template->language }}</dd><dt>النوع</dt><dd>{{ $template->layout_type }}</dd><dt>الحالة</dt><dd>{{ $template->is_active ? 'فعال' : 'معطل' }} @if($template->is_default) / افتراضي عام @endif</dd></dl><a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('company.invoice-templates.preview', [$company, $template]) }}">معاينة PDF</a></div></div>@endforeach</div>
<button class="btn btn-primary mt-4">حفظ القالب الافتراضي</button>
</form>
@endsection
