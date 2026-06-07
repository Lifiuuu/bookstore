@extends('layouts.app')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-qrcode-scan"></i>
        </span> Kunjungan Toko - Scanner Geolokasi
    </h3>
</div>

<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Scanner Barcode / QR</h4>
                <p class="text-muted">Arahkan kamera ke barcode toko. Sistem akan otomatis memvalidasi lokasi.</p>
                <div id="scanner" class="border rounded-3 overflow-hidden bg-dark" style="min-height: 380px;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Data Kunjungan</h4>

                <form id="visit-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Barcode Toko</label>
                        <input type="text" id="barcode" class="form-control bg-white" placeholder="Menunggu hasil scan..." readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Latitude Sales</label>
                            <input type="text" id="sales_latitude" class="form-control bg-white" placeholder="-" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Longitude Sales</label>
                            <input type="text" id="sales_longitude" class="form-control bg-white" placeholder="-" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Accuracy (m)</label>
                            <input type="text" id="sales_accuracy" class="form-control bg-white" placeholder="-" readonly>
                        </div>
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="small text-muted">Data toko terdeteksi</div>
                            <div class="fw-bold" id="shop-name">Belum ada scan</div>
                            <div class="small text-muted" id="shop-coordinates">-</div>
                        </div>
                    </div>
                </form>

                <div id="result-box" class="mt-4 d-none"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    const lookupBaseUrl = "{{ url('/api/kunjungan-toko') }}";
    const validateUrl = "{{ route('api.kunjungan_toko.validate') }}";
    const csrfToken = "{{ csrf_token() }}";

    const barcodeInput = document.getElementById('barcode');
    const salesLatitudeInput = document.getElementById('sales_latitude');
    const salesLongitudeInput = document.getElementById('sales_longitude');
    const salesAccuracyInput = document.getElementById('sales_accuracy');
    const shopName = document.getElementById('shop-name');
    const shopCoordinates = document.getElementById('shop-coordinates');
    const resultBox = document.getElementById('result-box');

    let currentToko = null;
    let scanner = null;
    let scannerStarted = false;

    // Fungsi untuk mereset seluruh form dan menyalakan kamera lagi
    window.resetUI = function() {
        currentToko = null;
        barcodeInput.value = '';
        salesLatitudeInput.value = '';
        salesLongitudeInput.value = '';
        salesAccuracyInput.value = '';
        shopName.textContent = 'Belum ada scan';
        shopCoordinates.textContent = '-';
        resetResult();
        scannerStarted = false;
        startScanner();
    };

    function showResult(html, type = 'info', showOkButton = false) {
        resultBox.className = 'mt-4 alert alert-' + type;
        
        let finalHtml = html;
        if (showOkButton) {
            finalHtml += `
                <div class="mt-3">
                    <button type="button" class="btn btn-${type === 'success' ? 'success' : 'danger'} btn-sm fw-bold" onclick="window.resetUI()">
                        <i class="mdi mdi-refresh"></i> OK, Mulai Ulang Scanner
                    </button>
                </div>
            `;
        }
        
        resultBox.innerHTML = finalHtml;
        resultBox.classList.remove('d-none');
    }

    function resetResult() {
        resultBox.className = 'mt-4 d-none';
        resultBox.innerHTML = '';
    }

    function renderToko(toko) {
        currentToko = toko;
        shopName.textContent = toko.nama_toko;
        shopCoordinates.textContent = `Barcode ${toko.barcode} | ${toko.latitude}, ${toko.longitude} | Akurasi master ${Number(toko.accuracy).toFixed(2)} m`;
        barcodeInput.value = toko.barcode;
    }

    async function fetchToko(barcode) {
        const response = await fetch(`${lookupBaseUrl}/${encodeURIComponent(barcode)}`, {
            headers: { 'Accept': 'application/json' }
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Barcode toko tidak ditemukan.');
        }

        renderToko(payload);
        return payload;
    }

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
                else fail(new Error('Gagal mendapatkan koordinat GPS yang akurat. Pastikan izin lokasi (GPS) diaktifkan.'));
            }, maxWaitMs);
        });
    }

    async function fillSalesPosition() {
        showResult('<i class="mdi mdi-loading mdi-spin"></i> Mencari lokasi GPS terbaik...', 'secondary');

        try {
            const position = await getAccuratePosition();
            salesLatitudeInput.value = position.latitude;
            salesLongitudeInput.value = position.longitude;
            salesAccuracyInput.value = position.accuracy.toFixed(2);
            showResult(`Lokasi sales didapat dengan akurasi ${position.accuracy.toFixed(2)} meter. Memproses validasi...`, 'info');
            return true;
        } catch (error) {
            showResult(error.message || 'Gagal mengambil lokasi sales.', 'danger', true);
            return false;
        }
    }

    async function startScanner() {
        if (scannerStarted) return;

        if (typeof Html5QrcodeScanner === 'undefined') {
            showResult('Library scanner belum termuat.', 'danger');
            return;
        }

        scanner = new Html5QrcodeScanner('scanner', {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
        });

        scanner.render(
            async (decodedText) => {
                scanner.clear().catch(() => {});
                scannerStarted = false;
                barcodeInput.value = decodedText.trim();
                
                try {
                    showResult('<i class="mdi mdi-loading mdi-spin"></i> Memeriksa barcode toko...', 'secondary');
                    await fetchToko(decodedText.trim());
                    
                    const locationSuccess = await fillSalesPosition();
                    
                    if (locationSuccess) {
                        await submitValidation(); 
                    }
                } catch (error) {
                    showResult(error.message || 'Barcode tidak ditemukan.', 'danger', true);
                }
            },
            () => {}
        );

        scannerStarted = true;
    }

    async function submitValidation() {
        try {
            const response = await fetch(validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    barcode: barcodeInput.value.trim(),
                    sales_latitude: salesLatitudeInput.value,
                    sales_longitude: salesLongitudeInput.value,
                    sales_accuracy: salesAccuracyInput.value,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                showResult(payload.message || 'Validasi gagal.', 'danger', true);
                return;
            }

            const alertType = payload.accepted ? 'success' : 'danger';
            showResult(`
                <div class="fw-bold mb-1 fs-5">${payload.message}</div>
                <div>Jarak aktual: <strong>${payload.distance_m} meter</strong></div>
                <div>Threshold efektif: ${payload.threshold_effective_m} meter</div>
                <div>Toko: ${payload.toko.nama_toko}</div>
            `, alertType, true); 

        } catch (error) {
            showResult('Terjadi kesalahan saat menghubungi server.', 'danger', true);
        }
    }

    // Hanya panggil scanner saat halaman dimuat, semua event click tombol manual dihapus
    document.addEventListener('DOMContentLoaded', startScanner);
</script>
@endpush