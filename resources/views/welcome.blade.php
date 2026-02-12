@extends('layouts.guest')

@section('content')
<div class="auth-form-wrapper p-5 text-center">
    <h1 class="font-weight-light mb-4">Welcome to Purple Admin</h1>
    <p class="mb-4">Please login or register to access the dashboard.</p>

    <div class="d-flex justify-content-center gap-3">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
        <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Register</a>
    </div>
</div>
@endsection
