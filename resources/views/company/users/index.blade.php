@extends('layouts.app')
@section('title', 'مستخدمو الشركة')
@section('content')
<x-layout.page-header :title="'مستخدمو '.$company->name_ar" subtitle="إدارة مستخدمي الشركة وأدوارهم."><x-slot:actions><a class="btn btn-primary" href="{{ route('company.users.create', $company) }}">مستخدم جديد</a></x-slot:actions></x-layout.page-header>
<div class="card"><table class="table mb-0"><tr><th>الاسم</th><th>البريد</th><th>الحالة</th><th>الأدوار</th><th></th></tr>@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->status }}</td><td>{{ $user->roles->pluck('name')->implode(', ') }}</td><td><a href="{{ route('company.users.show', [$company, $user]) }}">عرض</a></td></tr>@endforeach</table></div>{{ $users->links() }}
@endsection
