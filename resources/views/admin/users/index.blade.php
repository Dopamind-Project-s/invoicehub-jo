@extends('layouts.app')
@section('title','إدارة المستخدمين')
@section('content')
<x-layout.page-header title="إدارة المستخدمين" subtitle="مستخدمو النظام والمنشآت مع الأدوار المرتبطة."><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.users.create') }}">مستخدم جديد</a></x-slot:actions></x-layout.page-header>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>الاسم</th><th>البريد</th><th>المنشأة</th><th>النوع</th><th>الحالة</th><th>الأدوار</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->company?->name_ar ?: 'النظام' }}</td><td>{{ $user->role }}</td><td>{{ $user->status }}</td><td>{{ $user->roles->pluck('name')->implode(', ') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.show',$user) }}">عرض</a></td></tr>@endforeach</tbody></table></div></div>{{ $users->links() }}
@endsection
