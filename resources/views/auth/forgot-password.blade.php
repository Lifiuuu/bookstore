@extends('layouts.guest')

@section('content')
<div class="card">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Lupa Password</h3>

        <p class="text-muted small mb-4">
            Tidak ingat password? Tidak masalah! Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan reset password.
        </p>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="forms-sample">
            @csrf

            <!-- Email Address -->
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus />
                @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('login') }}" class="text-small">Kembali ke login</a>
                <button type="submit" class="btn btn-primary">Kirim Link Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection
