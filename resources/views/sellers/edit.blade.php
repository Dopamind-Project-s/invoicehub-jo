@extends('layouts.app')
@section('content')
<h1>تعديل بائع</h1>
<form method="post" action="{{ route('sellers.update', $seller) }}" enctype="multipart/form-data" class="card card-body">
    @method('PUT')
    @include('sellers._form')
</form>
@endsection
