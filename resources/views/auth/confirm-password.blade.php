@extends('layouts.guest')

@section('content')
<div class="card">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Konfirmasi Password</h3>

        <p class="text-muted small mb-4">
            Ini adalah area aman aplikasi. Harap konfirmasi password Anda sebelum melanjutkan.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}" class="forms-sample">
            @csrf

            <!-- Password -->
            <div class="form-group mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" />
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>
@endsection
