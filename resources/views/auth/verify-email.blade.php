@extends('layouts.guest')

@section('content')
<div class="card">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Verifikasi Email</h3>

        <p class="text-muted small mb-4">
            Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email dengan mengklik tautan yang baru saja kami kirim? Jika Anda tidak menerima email, kami dengan senang hati akan mengirimkan yang lain.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Tautan verifikasi baru telah dikirim ke alamat email Anda.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Kirim Ulang Email Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
            </form>
        </div>
    </div>
</div>
@endsection
