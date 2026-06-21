@extends('layouts.app')
@section('title', 'مفاتيح المزايا')
@section('content')
<x-layout.page-header title="مفاتيح المزايا" subtitle="تعريف المزايا القابلة للربط بالمنشآت والباقات." />
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>الكود</th><th>الاسم العربي</th><th>English</th><th>الفئة</th><th>الوصف</th><th>الاستخدام</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($features as $feature)
                <tr>
                    <td><code>{{ $feature->code }}</code></td>
                    <td>{{ $feature->name_ar ?: $feature->name }}</td>
                    <td>{{ $feature->name_en ?: $feature->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $feature->category ?: 'core' }}</span></td>
                    <td>{{ $feature->description }}</td>
                    <td>{{ $feature->companies_count }} منشأة / {{ $feature->plans_count }} باقة</td>
                    <td>{{ $feature->is_active ? 'فعالة' : 'معطلة' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $features->links() }}</div>
</div>
@endsection
