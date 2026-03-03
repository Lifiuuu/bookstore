@if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.request') || request()->routeIs('password.reset') || request()->routeIs('otp.show') || request()->routeIs('otp.verify'))
    <!-- Simple full-width wrapper for auth pages (no sidebar spacing) -->
    <div class="container-fluid">
        <div class="main-panel">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>
@else
    <div class="container-fluid page-body-wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Main Panel -->
        <div class="main-panel">
            <div class="content-wrapper">
                @yield('content')
            </div>
            <!-- content-wrapper ends -->
            
            <!-- Footer -->
            @include('layouts.footer')
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
@endif
