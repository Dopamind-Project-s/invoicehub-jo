@extends('layouts.company-workspace')
@section('title', 'إعدادات المنشأة')
@section('content')
<x-layout.page-header :title="'إعدادات '.$company->name_ar" subtitle="إعدادات الهوية والفواتير الخاصة بالمنشأة." />
<form method="post" action="{{ route('company.settings.update', $company) }}" enctype="multipart/form-data" class="card card-body">
    @csrf @method('PUT')
    @foreach($categories as $category => $items)
        <h2 class="h5 mt-3">{{ $category }}</h2>
        <div class="row g-3">
            @foreach($items as $key => $label)
                @php($value = old('settings.'.$key, $settings[$key]->value ?? ''))
                <div class="col-md-6">
                    <label class="form-label">{{ $label }}</label>
                    @if(in_array($key, ['invoice_logo', 'invoice_stamp_image', 'company_logo'], true))
                        <input type="hidden" name="settings[{{ $key }}]" value="{{ $value }}">
                        <input class="form-control" type="file" accept="image/*" name="{{ $key === 'invoice_stamp_image' ? 'invoice_stamp_image_file' : ($key === 'company_logo' ? 'company_logo_file' : 'invoice_logo_file') }}">
                        @if($value)<img src="{{ asset('storage/'.$value) }}" alt="{{ $label }}" class="mt-2 rounded border" style="max-height:72px">@endif
                    @elseif(str_contains($key, 'color'))
                        <input class="form-control form-control-color" type="color" name="settings[{{ $key }}]" value="{{ $value ?: '#1f6feb' }}">
                    @elseif($key === 'default_language')
                        <select class="form-select" name="settings[{{ $key }}]"><option value="ar" @selected($value === 'ar')>العربية</option><option value="en" @selected($value === 'en')>English</option></select>
                    @elseif($key === 'jofotara_mode')
                        <select class="form-select" name="settings[{{ $key }}]"><option value="sandbox" @selected($value === 'sandbox')>تجريبي</option><option value="production" @selected($value === 'production')>إنتاجي</option></select>
                    @elseif(in_array($key, ['invoice_footer_text', 'invoice_terms_and_conditions', 'invoice_signature_block'], true))
                        <textarea class="form-control" rows="3" name="settings[{{ $key }}]">{{ $value }}</textarea>
                    @else
                        <input class="form-control" name="settings[{{ $key }}]" value="{{ $value }}">
                    @endif
                    <div class="form-text"><code>{{ $key }}</code></div>
                </div>
            @endforeach
        </div>
    @endforeach
    <button class="btn btn-primary mt-4">حفظ</button>
</form>
@endsection
