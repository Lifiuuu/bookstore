<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purple Admin</title>
    
    <!-- Global Styles -->
    @include('layouts.styleglobal')
    
    <!-- Page Specific Styles -->
    @include('layouts.stylepage')
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="/assets/images/favicon.png" />

    <!-- Vite assets (JS & CSS) -->
    @if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.request') || request()->routeIs('password.reset') || request()->routeIs('otp.show') || request()->routeIs('otp.verify'))
    <style>
        /* Override admin shell styles for auth pages so forms can be fullscreen */
        .content-wrapper { background: transparent !important; padding: 0 !important; }
        .page-body-wrapper { padding-left: 0 !important; padding-right: 0 !important; }
        .main-panel { width: 100% !important; min-height: 100vh !important; }
    </style>
    @endif
</head>
<body>
    <div class="container-scroller">
        <!-- Navbar: hide on auth pages (login/register/password) -->
        @unless(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.request') || request()->routeIs('password.reset') || request()->routeIs('otp.show') || request()->routeIs('otp.verify'))
            @include('layouts.navbar')
        @endunless

        <!-- Always include content (content partial will hide sidebar/footer on auth pages) -->
        @include('layouts.content')
    </div>
    <!-- container-scroller -->
    
    <!-- Global JavaScript -->
    @include('layouts.javascriptglobal')
    
    <!-- Page Specific JavaScript -->
    @include('layouts.javascriptpage')
    
    {{-- Page scripts pushed from views (e.g. DataTables) --}}
    @stack('scripts')
</body>
</html>
