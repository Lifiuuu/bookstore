@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Pendaftaran Antrian Pasien</h4>
            </div>
            <div class="card-body">
                
                @if(session('queued'))
                    @php $q = session('queued'); @endphp
                    <div class="alert alert-success text-center" role="alert">
                        <h4 class="alert-heading">Berhasil Mendaftar!</h4>
                        <p>Nomor Antrian Anda:</p>
                        <h1 class="display-1 fw-bold">{{ $q['number'] }}</h1>
                        <p class="mb-0">Poliklinik: <strong>{{ $q['poli_name'] }}</strong></p>
                        <p class="mb-0">Estimasi waktu tunggu: ± {{ $q['estimated'] }} menit (posisi ke-{{ $q['position'] }})</p>
                        <hr>
                        <a href="{{ route('hq.register') }}" class="btn btn-primary">Daftarkan Pasien Lain</a>
                    </div>
                @else
                    <form action="{{ route('hq.register.submit') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="patient-name">Nama Lengkap Pasien</label>
                            <input type="text" id="patient-name" name="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" placeholder="Masukkan nama lengkap pasien" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="poli-select">Poliklinik Tujuan</label>
                            <select id="poli-select" name="poli" class="form-control @error('poli') is-invalid @enderror">
                                @foreach($polyclinics as $poli)
                                    <option value="{{ $poli['id'] }}" {{ old('poli') === $poli['id'] ? 'selected' : '' }}>
                                        {{ $poli['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poli')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="d-block">Kategori Prioritas</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="p-normal" value="normal" {{ old('priority', 'normal') === 'normal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="p-normal">Normal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="p-lansia" value="lansia" {{ old('priority') === 'lansia' ? 'checked' : '' }}>
                                <label class="form-check-label" for="p-lansia">Lansia</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="p-disabilitas" value="disabilitas" {{ old('priority') === 'disabilitas' ? 'checked' : '' }}>
                                <label class="form-check-label" for="p-disabilitas">Disabilitas</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="p-darurat" value="darurat" {{ old('priority') === 'darurat' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-bold" for="p-darurat">Darurat</label>
                            </div>
                            @error('priority')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Ambil Nomor Antrian</button>
                    </form>
                @endif
                
            </div>
        </div>
    </div>
</div>
@endsection
