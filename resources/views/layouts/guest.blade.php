@include('layouts.guest.head')
<body>
<div id="landing">
    @include('layouts.guest.navbar')
    <main class="guest-main">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>
    @include('layouts.guest.footer')
</div>
@include('layouts.guest.login-offcanvas')
@include('layouts.guest.scripts')
</body>
</html>
