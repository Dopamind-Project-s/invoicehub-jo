@extends('layouts.app')
@section('title', 'الأدوار والصلاحيات')
@section('content')
<x-layout.page-header :title="'أدوار '.$company->name_ar" subtitle="صلاحيات معزولة لكل منشأة باستخدام فرق Spatie." />
@foreach($roles as $role)
<form method="post" action="{{ route('company.roles.update', [$company, $role]) }}" class="card card-body mb-3">@csrf @method('PUT')<h2 class="h5">{{ $role->name }}</h2><div class="row g-2">@foreach($permissions as $permission)<div class="col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($role->permissions->contains('name', $permission->name))> {{ $permission->name }}</label></div>@endforeach</div><div><button class="btn btn-primary mt-3">حفظ</button></div></form>
@endforeach
@endsection
