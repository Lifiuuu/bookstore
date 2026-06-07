@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">Registrasi Kartu NFC Mahasiswa</h4>
                <p class="text-muted text-center mb-4">
                    Pilih mahasiswa dan tap kartu NFC (KTM) ke belakang HP untuk mendaftarkan kartu.
                </p>

                <div id="status-card" class="alert alert-secondary text-center rounded-lg p-3 mb-4 transition-colors duration-300">
                    <span id="status-icon" class="mdi mdi-information-outline text-xl align-middle mr-2"></span>
                    <span id="status-text" class="font-weight-bold">Step 1: Pilih Mahasiswa</span>
                </div>

                <form id="nfc-register-form">
                    @csrf
                    <div class="form-group text-center mb-4">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="student_type" id="type_existing" value="existing" checked> Pilih Mahasiswa Lama <i class="input-helper"></i>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="student_type" id="type_new" value="new"> Daftar Mahasiswa Baru <i class="input-helper"></i>
                            </label>
                        </div>
                    </div>

                    <div id="existing_student_fields" class="form-group">
                        <label for="student_id">Pilih Mahasiswa</label>
                        <select class="form-control form-control-lg" id="student_id" name="student_id" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->nim }} - {{ $student->name }}</option>
                            @endforeach
                        </select>
                        @if($students->isEmpty())
                            <small class="form-text text-danger">Semua mahasiswa yang ada sudah memiliki kartu NFC. Silakan tambah mahasiswa baru.</small>
                        @endif
                    </div>

                    <div id="new_student_fields" class="d-none">
                        <div class="form-group">
                            <label for="nim">NIM</label>
                            <input type="text" class="form-control form-control-lg" id="nim" name="nim" placeholder="Masukkan NIM">
                        </div>
                        <div class="form-group">
                            <label for="name">Nama Mahasiswa</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="Masukkan Nama Lengkap">
                        </div>
                    </div>

                    <div class="form-group text-center my-4">
                        <button type="button" id="btn-scan" class="btn btn-gradient-primary btn-lg btn-block" disabled>
                            <i class="mdi mdi-nfc"></i> Tap to Scan Card
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="nfc_serial_display">NFC Serial Number</label>
                        <input type="text" class="form-control form-control-lg text-center font-monospace" id="nfc_serial_display" placeholder="Menunggu scan..." readonly>
                        <input type="hidden" id="nfc_serial_number" name="nfc_serial_number">
                    </div>

                    <div class="form-group text-center mt-5">
                        <button type="submit" id="btn-submit" class="btn btn-gradient-success btn-lg btn-block" disabled>
                            <i class="mdi mdi-content-save"></i> Simpan Registrasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const studentSelect = document.getElementById('student_id');
        const btnScan = document.getElementById('btn-scan');
        const btnSubmit = document.getElementById('btn-submit');
        const nfcSerialDisplay = document.getElementById('nfc_serial_display');
        const nfcSerialNumber = document.getElementById('nfc_serial_number');
        const statusCard = document.getElementById('status-card');
        const statusText = document.getElementById('status-text');
        const statusIcon = document.getElementById('status-icon');
        const registerForm = document.getElementById('nfc-register-form');

        const typeRadios = document.querySelectorAll('input[name="student_type"]');
        const existingFields = document.getElementById('existing_student_fields');
        const newFields = document.getElementById('new_student_fields');
        const nimInput = document.getElementById('nim');
        const nameInput = document.getElementById('name');

        let ndef = null;
        let isScanning = false;

        function validateReadyToScan() {
            const isNew = document.getElementById('type_new').checked;
            let ready = false;
            if (isNew) {
                ready = nimInput.value.trim() !== '' && nameInput.value.trim() !== '';
            } else {
                ready = studentSelect.value !== '';
            }
            
            btnScan.disabled = !ready;
            if(!ready) {
                btnSubmit.disabled = true;
                updateStatus('Step 1: Lengkapi Data Mahasiswa', 'alert-secondary', 'mdi-information-outline');
            } else if (!isScanning && !nfcSerialNumber.value) {
                updateStatus('Step 2: Tap Scan Card', 'alert-info', 'mdi-nfc-tap');
            }
        }

        typeRadios.forEach(r => r.addEventListener('change', (e) => {
            if (e.target.value === 'new') {
                existingFields.classList.add('d-none');
                newFields.classList.remove('d-none');
                studentSelect.required = false;
                nimInput.required = true;
                nameInput.required = true;
            } else {
                existingFields.classList.remove('d-none');
                newFields.classList.add('d-none');
                studentSelect.required = true;
                nimInput.required = false;
                nameInput.required = false;
            }
            validateReadyToScan();
        }));

        studentSelect.addEventListener('change', validateReadyToScan);
        nimInput.addEventListener('input', validateReadyToScan);
        nameInput.addEventListener('input', validateReadyToScan);

        // Function to update visual status
        function updateStatus(text, bgClass, iconClass) {
            statusText.innerText = text;
            statusCard.className = `alert ${bgClass} text-center rounded-lg p-3 mb-4 transition-colors duration-300`;
            statusIcon.className = `mdi ${iconClass} text-xl align-middle mr-2`;
        }

        // Initialize NFC Scan
        btnScan.addEventListener('click', async () => {
            if (!("NDEFReader" in window)) {
                alert("Browser atau perangkat ini tidak mendukung Web NFC API. Gunakan Chrome di Android.");
                return;
            }

            try {
                ndef = new NDEFReader();
                await ndef.scan();
                isScanning = true;
                updateStatus('NFC Antenna Active. Dekatkan kartu ke belakang HP...', 'alert-warning', 'mdi-cellphone-nfc');
                btnScan.innerText = "Scanning...";
                btnScan.classList.replace('btn-gradient-primary', 'btn-gradient-warning');
                btnScan.disabled = true;

                ndef.addEventListener("readingerror", () => {
                    updateStatus('Gagal membaca kartu. Coba lagi.', 'alert-danger', 'mdi-alert-circle');
                });

                ndef.addEventListener("reading", ({ message, serialNumber }) => {
                    // Normalize serial number format if needed
                    const normalizedSerial = serialNumber.toUpperCase();
                    
                    nfcSerialDisplay.value = normalizedSerial;
                    nfcSerialNumber.value = normalizedSerial;
                    
                    updateStatus('Kartu berhasil dibaca! Siap disimpan.', 'alert-success', 'mdi-check-circle');
                    
                    btnSubmit.disabled = false;
                    btnScan.innerText = "Scan Ulang Kartu";
                    btnScan.classList.replace('btn-gradient-warning', 'btn-gradient-primary');
                    btnScan.disabled = false;
                    isScanning = false;
                });

            } catch (error) {
                console.error("NFC Scan Error:", error);
                if (error.name === 'NotAllowedError') {
                    alert("Izin NFC ditolak. Mohon izinkan akses NFC di browser Anda.");
                } else {
                    alert("Gagal memulai NFC: " + error.message);
                }
                updateStatus('Error memulai NFC.', 'alert-danger', 'mdi-alert');
                btnScan.disabled = false;
            }
        });

        // Submit form via fetch
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!nfcSerialNumber.value) {
                alert("Silakan scan kartu NFC terlebih dahulu!");
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';

            try {
                const response = await fetch('/api/student/assign-nfc', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_new_student: document.getElementById('type_new').checked,
                        student_id: studentSelect.value,
                        nim: nimInput.value,
                        name: nameInput.value,
                        nfc_serial_number: nfcSerialNumber.value
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message);
                    // Reload to update dropdown (remove registered student)
                    window.location.reload();
                } else {
                    alert("Gagal menyimpan: " + (data.message || 'Validation error'));
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="mdi mdi-content-save"></i> Simpan Registrasi';
                }
            } catch (error) {
                console.error("Submit Error:", error);
                alert("Terjadi kesalahan jaringan.");
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="mdi mdi-content-save"></i> Simpan Registrasi';
            }
        });
    });
</script>
@endpush
