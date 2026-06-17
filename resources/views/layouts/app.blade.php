<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InvoiceHub Jo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #f7f7f7; }
        .card { border-radius: 14px; }
        .table th { background: #f0f3f8; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('invoices.index') }}">InvoiceHub Jo</a>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-light" href="{{ route('sellers.index') }}">البائعين</a>
            <a class="btn btn-sm btn-light" href="{{ route('customers.index') }}">العملاء</a>
            <a class="btn btn-sm btn-light" href="{{ route('invoices.index') }}">الفواتير</a>
        </div>
    </div>
</nav>
<main class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
