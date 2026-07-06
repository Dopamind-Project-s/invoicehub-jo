@extends('layouts.app')
@section('title','الأدوار والصلاحيات')
@section('content')
<x-layout.page-header title="الأدوار والصلاحيات" subtitle="إدارة Spatie Roles وربطها بالصلاحيات المنظمة."><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.roles.create') }}">دور جديد</a></x-slot:actions></x-layout.page-header>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>الدور</th><th>النطاق</th><th>الصلاحيات</th><th>المستخدمون</th><th></th></tr></thead><tbody>@foreach($roles as $role)<tr><td>{{ $role->name }}</td><td>{{ $role->company_id ? 'منشأة #'.$role->company_id : 'نظام' }}</td><td>{{ $role->permissions_count }}</td><td>{{ $role->users_count }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.roles.show',$role) }}">عرض</a></td></tr>@endforeach</tbody></table></div></div>{{ $roles->links() }}
@endsection
