@extends('layouts.app')
@section('title', 'إعدادات الشركة')
@section('content')
<x-layout.page-header :title="'إعدادات '.$company->name_ar" subtitle="إعدادات مرنة مفتاحية/قيمية للمراحل القادمة." />
<form method="post" action="{{ route('company.settings.update', $company) }}" class="card card-body">@csrf @method('PUT')@foreach($categories as $category => $items)<h2 class="h5 mt-3">{{ $category }}</h2><div class="row g-3">@foreach($items as $key => $label)<div class="col-md-6"><label class="form-label">{{ $label }} <code>{{ $key }}</code></label><input class="form-control" name="settings[{{ $key }}]" value="{{ old('settings.'.$key, $settings[$key]->value ?? '') }}"></div>@endforeach</div>@endforeach<button class="btn btn-primary mt-4">حفظ</button></form>
@endsection
