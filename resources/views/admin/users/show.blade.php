@extends('layouts.app')
@section('title','عرض مستخدم')
@section('content')
<x-layout.page-header :title="$user->name" subtitle="تفاصيل المستخدم."><x-slot:actions><a class="btn btn-outline-primary" href="{{ route('admin.users.edit',$user) }}">تعديل</a><form method="post" action="{{ route('admin.users.suspend',$user) }}">@csrf<button class="btn btn-warning">تعطيل</button></form><form method="post" action="{{ route('admin.users.destroy',$user) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">حذف</button></form></x-slot:actions></x-layout.page-header>
<div class="card card-body"><dl class="row"><dt class="col-3">البريد</dt><dd class="col-9">{{ $user->email }}</dd><dt class="col-3">المنشأة</dt><dd class="col-9">{{ $user->company?->name_ar ?: 'النظام' }}</dd><dt class="col-3">الحالة</dt><dd class="col-9">{{ $user->status }}</dd><dt class="col-3">الأدوار</dt><dd class="col-9">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</dd></dl></div>
@endsection
