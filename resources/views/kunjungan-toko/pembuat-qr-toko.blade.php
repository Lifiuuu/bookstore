@extends('layouts.app')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-map-marker-radius"></i>
        </span> Kunjungan Toko - Master Lokasi
    </h3>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $editingToko ? 'Edit Titik Awal Toko' : 'Tambah Titik Awal Toko' }}</h4>
                <p class="text-muted">Barcode 8 karakter akan dipakai oleh sales saat scan di toko.</p>

                <form method="POST" action="{{ $editingToko ? route('kunjungan_toko.update', $editingToko) : route('kunjungan_toko.store') }}">
                    @csrf
                    @if($editingToko)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Barcode (Otomatis)</label>
                        <input type="text" name="barcode" class="form-control bg-white" value="{{ old('barcode', $editingToko->barcode ?? $newBarcode) }}" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Toko</label>
                        <input type="text" name="nama_toko" class="form-control @error('nama_toko') is-invalid @enderror" value="{{ old('nama_toko', $editingToko->nama_toko ?? '') }}" maxlength="50" required>
                        @error('nama_toko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" id="admin_latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $editingToko->latitude ?? '') }}" required>
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" id="admin_longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $editingToko->longitude ?? '') }}" required>
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Accuracy (m)</label>
                            <input type="number" step="any" name="accuracy" id="admin_accuracy" class="form-control @error('accuracy') is-invalid @enderror" value="{{ old('accuracy', $editingToko->accuracy ?? 0) }}" required>
                            @error('accuracy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <button type="button" id="btn-get-location" class="btn btn-outline-info btn-sm w-100">
                            <i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi Saat Ini
                        </button>
                        <small id="location-status" class="text-muted d-block mt-1"></small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ $editingToko ? 'Perbarui' : 'Simpan' }}</button>
                        @if($editingToko)
                            <a href="{{ route('kunjungan_toko.index') }}" class="btn btn-light">Batal</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-0">Daftar Toko</h4>
                        <small class="text-muted">Cetak barcode/QR untuk ditempel di lokasi fisik toko.</small>
                    </div>
                    <a href="{{ route('kunjungan_toko.scanner') }}" class="btn btn-success">Buka Halaman Sales</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Toko</th>
                                <th>Koordinat</th>
                                <th>Akurasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokoList as $toko)
                                <tr>
                                    <td><strong>{{ $toko->barcode }}</strong></td>
                                    <td>{{ $toko->nama_toko }}</td>
                                    <td>
                                        <small class="text-muted d-block">{{ $toko->latitude }}</small>
                                        <small class="text-muted d-block">{{ $toko->longitude }}</small>
                                    </td>
                                    <td>{{ number_format($toko->accuracy, 2) }} m</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('kunjungan_toko.edit', $toko) }}" class="btn btn-warning">Edit</a>
                                            <a href="{{ route('kunjungan_toko.print', $toko) }}" class="btn btn-info" target="_blank">Cetak</a>
                                            <form action="{{ route('kunjungan_toko.destroy', $toko) }}" method="POST" onsubmit="return confirm('Hapus data toko ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data lokasi toko.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi pencari lokasi yang sama dengan halaman scanner
    function getAccuratePosition({ targetAccuracy = 30, timeoutMs = 20000, maxWaitMs = 30000 } = {}) {
        if (!navigator.geolocation) {
            return Promise.reject(new Error('Browser tidak mendukung geolocation.'));
        }

        return new Promise((resolve, reject) => {
            let bestPosition = null;
            let resolved = false;
            let watchId = null;

            const finish = () => {
                if (resolved) return;
                resolved = true;
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                clearTimeout(timeoutId);
                resolve(bestPosition);
            };

            const fail = (error) => {
                if (resolved) return;
                resolved = true;
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                clearTimeout(timeoutId);
                reject(error);
            };

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const candidate = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                    };

                    if (!bestPosition || candidate.accuracy < bestPosition.accuracy) {
                        bestPosition = candidate;
                    }

                    if (candidate.accuracy <= targetAccuracy) {
                        finish();
                    }
                },
                (error) => {
                    if (!bestPosition) fail(error);
                },
                { enableHighAccuracy: true, maximumAge: 0, timeout: timeoutMs }
            );

            const timeoutId = setTimeout(() => {
                if (bestPosition) finish();
                else fail(new Error('Gagal mendapatkan koordinat GPS.'));
            }, maxWaitMs);
        });
    }

    document.getElementById('btn-get-location').addEventListener('click', async function() {
        const statusText = document.getElementById('location-status');
        const btn = this;
        
        // Ubah tampilan tombol saat loading
        btn.disabled = true;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Sedang mencari lokasi presisi...';
        statusText.innerHTML = '';
        statusText.className = 'text-muted d-block mt-1';

        try {
            // Panggil fungsi pencari lokasi
            const position = await getAccuratePosition();
            
            // Isi form otomatis
            document.getElementById('admin_latitude').value = position.latitude;
            document.getElementById('admin_longitude').value = position.longitude;
            
            // Dibulatkan 2 angka di belakang koma untuk akurasi
            document.getElementById('admin_accuracy').value = position.accuracy.toFixed(2); 
            
            statusText.innerHTML = '<i class="mdi mdi-check-circle"></i> Lokasi berhasil disalin ke form.';
            statusText.className = 'text-success d-block mt-1';
        } catch (error) {
            statusText.innerHTML = '<i class="mdi mdi-alert-circle"></i> ' + (error.message || 'Gagal mengambil lokasi.');
            statusText.className = 'text-danger d-block mt-1';
        } finally {
            // Kembalikan tombol ke semula
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi Saat Ini';
        }
    });
</script>
@endpush