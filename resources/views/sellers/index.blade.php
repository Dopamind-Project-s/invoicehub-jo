@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>البائعين</h1>
    <a class="btn btn-primary" href="{{ route('sellers.create') }}">بائع جديد</a>
</div>
<div class="card">
    <table class="table mb-0">
        <tr><th>الاسم</th><th>الرقم الضريبي</th><th>تسلسل الدخل</th><th>افتراضي</th><th></th></tr>
        @foreach($sellers as $seller)
            <tr>
                <td>{{ $seller->name }}</td>
                <td>{{ $seller->tax_number }}</td>
                <td>{{ $seller->income_source_sequence }}</td>
                <td>{{ $seller->is_default ? 'نعم' : 'لا' }}</td>
                <td><a href="{{ route('sellers.edit', $seller) }}">تعديل</a></td>
            </tr>
        @endforeach
    </table>
</div>
{{ $sellers->links() }}
@endsection
