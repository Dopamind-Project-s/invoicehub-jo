@extends('layouts.app')
@section('title', 'قوالب فواتير المنشأة')
@section('content')
<x-layout.page-header title="قوالب فواتير المنشأة" subtitle="اختر القالب الافتراضي للطباعة والمشاركة." />
<form method="post" action="{{ route('company.invoice-templates.update', $company) }}" class="card card-body">@csrf @method('PUT')
<div class="row g-3">@foreach($templates as $template)<div class="col-md-4"><label class="form-check border rounded p-3 h-100"><input class="form-check-input" type="radio" name="invoice_template_id" value="{{ $template->id }}" @checked((string)$selected === (string)$template->id || (!$selected && $template->is_default))><span class="form-check-label"><strong>{{ $template->name }}</strong><br><small class="text-muted">{{ $template->language }} / {{ $template->layout_type }}</small></span></label></div>@endforeach</div>
<button class="btn btn-primary mt-4">حفظ القالب الافتراضي</button>
</form>
@endsection
