@extends('layouts.app')
@section('title', 'الأسئلة الشائعة')
@section('page_title', 'الموقع الإلكتروني')
@section('content')
<x-layout.page-header title="الأسئلة الشائعة" subtitle="إدارة الأسئلة التي تظهر في صفحة الهبوط." />
<div class="row g-4"><div class="col-lg-5"><form method="post" action="{{ route('admin.landing-cms.faqs.store') }}" class="card card-body"><h2 class="h5">إضافة سؤال</h2>@include('admin.landing-cms.faqs._form')</form></div><div class="col-lg-7"><div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>السؤال</th><th>التصنيف</th><th>الحالة</th><th></th></tr></thead><tbody>@foreach($faqs as $row)<tr><td>{{ $row->question_ar }}<br><small>{{ $row->answer_ar }}</small></td><td>{{ $row->category }}</td><td>{{ $row->is_active ? 'فعال' : 'مخفي' }}</td><td class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.landing-cms.faqs.edit', $row) }}">تعديل</a><form method="post" action="{{ route('admin.landing-cms.faqs.destroy', $row) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form></td></tr>@endforeach</tbody></table></div></div>{{ $faqs->links() }}</div></div>
@endsection
