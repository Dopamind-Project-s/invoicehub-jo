@extends('layouts.app')
@section('title','عرض دور')
@section('content')
<x-layout.page-header :title="$role->name" subtitle="تفاصيل الدور وصلاحياته."><x-slot:actions><a class="btn btn-outline-primary" href="{{ route('admin.roles.edit',$role) }}">تعديل</a><form method="post" action="{{ route('admin.roles.destroy',$role) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">حذف</button></form></x-slot:actions></x-layout.page-header>
<div class="card card-body"><p>النطاق: {{ $role->company_id ? 'منشأة #'.$role->company_id : 'نظام' }}</p><h2 class="h5">الصلاحيات</h2><div class="d-flex flex-wrap gap-2">@foreach($role->permissions as $permission)<span class="badge bg-primary-subtle text-primary border">{{ $permission->name }}</span>@endforeach</div></div>
@endsection
