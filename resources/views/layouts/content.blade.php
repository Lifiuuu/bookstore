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
