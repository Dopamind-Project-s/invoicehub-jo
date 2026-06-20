@extends('layouts.app')
@section('title', 'مفاتيح المزايا')
@section('content')
<x-layout.page-header title="مفاتيح المزايا" subtitle="قائمة المزايا التي يمكن ربطها بكل منشأة." />
<div class="card"><table class="table mb-0"><tr><th>الكود</th><th>الاسم</th><th>الوصف</th><th>الحالة</th></tr>@foreach($features as $feature)<tr><td>{{ $feature->code }}</td><td>{{ $feature->name }}</td><td>{{ $feature->description }}</td><td>{{ $feature->is_active ? 'فعالة' : 'معطلة' }}</td></tr>@endforeach</table></div>
@endsection
