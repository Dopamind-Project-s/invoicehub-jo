@extends('layouts.app')
@section('title', 'الإعدادات العامة للموقع')
@section('page_title', 'الموقع الإلكتروني')
@section('content')
<x-layout.page-header title="الإعدادات العامة" subtitle="إدارة إعدادات التواصل والتذييل وSEO وCTA للموقع." />
<form method="post" action="{{ route('admin.landing-cms.settings.update') }}" class="card card-body">
    @csrf @method('PUT')
    <div class="row g-3">
        @foreach($settings as $setting)
            <div class="col-md-6">
                <label class="form-label">{{ $setting->group }}.{{ $setting->key }} @if($setting->locale)<small>({{ $setting->locale }})</small>@endif</label>
                @if($setting->type === 'textarea')
                    <textarea name="settings[{{ $setting->id }}]" class="form-control" rows="3">{{ old('settings.'.$setting->id, $setting->value) }}</textarea>
                @else
                    <input name="settings[{{ $setting->id }}]" class="form-control" value="{{ old('settings.'.$setting->id, $setting->value) }}">
                @endif
            </div>
        @endforeach
    </div>
    <button class="btn btn-primary mt-4">حفظ الإعدادات</button>
</form>
@endsection
