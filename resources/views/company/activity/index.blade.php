@extends('layouts.app')
@section('title', 'مركز النشاط')
@section('content')
<x-layout.page-header :title="'مركز نشاط '.$company->name_ar" subtitle="سجل نشاطات الشركة والمستخدمين من audit_logs." />
<form class="card card-body mb-3"><div class="row g-2"><div class="col-md-3"><input name="date" type="date" class="form-control" value="{{ request('date') }}"></div><div class="col-md-3"><input name="action" class="form-control" placeholder="الإجراء" value="{{ request('action') }}"></div><div class="col-md-3"><select name="user_id" class="form-select"><option value="">كل المستخدمين</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select></div><div class="col-md-3"><button class="btn btn-primary w-100">تصفية</button></div></div></form>
<div class="card"><table class="table mb-0"><tr><th>التاريخ</th><th>المستخدم</th><th>الإجراء</th><th>الموديل</th></tr>@foreach($activities as $activity)<tr><td>{{ $activity->created_at?->format('Y-m-d H:i') }}</td><td>{{ $activity->user?->name ?: '—' }}</td><td>{{ $activity->action }}</td><td>{{ class_basename($activity->auditable_type) }} #{{ $activity->auditable_id }}</td></tr>@endforeach</table></div>{{ $activities->links() }}
@endsection
