@extends('layouts.app')

@section('content')
<div class="container auth-container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8">
            <div class="card auth-card">
                <div class="card-body p-4" >
                    <h3 class="card-title text-center mb-3">Sign in to your account</h3>
                    <p class="text-center text-muted small mb-4">Enter your email and password or continue with Google</p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                            @endif
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </form>

                    <div class="separator text-center my-3"><small class="text-muted">or</small></div>

                    <div class="d-grid mb-2">
                        <a href="{{ route('login.google') }}" class="btn google-btn"> 
                            <span class="google-logo" aria-hidden="true"></span>
                            Continue with Google
                        </a>
                    </div>

                    <div class="text-center mt-3 small">
                        Don't have an account? <a href="{{ route('register') }}">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-container{ min-height:70vh; display:flex; align-items:center; justify-content:center; padding:40px 0; }
    .auth-card{ border-radius:0; max-width:7000px; width:100%; margin:0 auto; box-shadow:none; border:1px solid #e9ecef; }
    .separator{ position:relative; }
    .separator::before{ content:""; position:absolute; left:8%; right:8%; top:50%; height:1px; background:#e9ecef; z-index:0; }
    .separator small{ position:relative; z-index:1; padding:0 12px; background:#fff; }
    .google-btn{ background:#fff; color:#444; border:1px solid #ddd; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
    .auth-card .card-body{ padding:32px; }
    .google-logo{ width:18px; height:18px; background-image: url('/assets/images/google-icon.png'); background-size:contain; display:inline-block; }
    @media (max-width:576px){ .auth-card{ margin:0 10px; max-width:100%; } }
</style>

@endsection