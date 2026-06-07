@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0 text-white">NFC Student Attendance</h4>
            </div>
            <div class="card-body text-center p-5">
                
                <div class="mb-4">
                    <i class="mdi mdi-cellphone-nfc text-primary" style="font-size: 80px;"></i>
                </div>

                <h3 class="font-weight-bold mb-3" id="status-title">Ready to Scan</h3>
                <p class="text-muted mb-4" id="status-desc">Click the button below to start the NFC scanner, then tap the student card on the back of your device.</p>

                <div id="result-card" class="alert d-none mb-4 text-left" role="alert">
                    <!-- Dynamic content -->
                </div>

                <button id="start-scan-btn" class="btn btn-primary btn-lg btn-block shadow-sm">
                    <i class="mdi mdi-nfc me-2"></i> Start NFC Scanner
                </button>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const startBtn = document.getElementById('start-scan-btn');
        const statusTitle = document.getElementById('status-title');
        const statusDesc = document.getElementById('status-desc');
        const resultCard = document.getElementById('result-card');

        // Check if Web NFC is supported
        if (!('NDEFReader' in window)) {
            startBtn.disabled = true;
            statusTitle.textContent = 'Browser Not Supported';
            statusTitle.classList.add('text-danger');
            statusDesc.textContent = 'Web NFC is not supported in this browser. Please use Chrome on Android.';
            return;
        }

        startBtn.addEventListener('click', async () => {
            try {
                // Reset UI
                resultCard.classList.add('d-none');
                resultCard.className = 'alert d-none mb-4 text-left';
                
                statusTitle.textContent = 'Scanning...';
                statusTitle.className = 'font-weight-bold mb-3 text-warning';
                statusDesc.textContent = 'Please tap the NFC card on the back of your device.';
                startBtn.disabled = true;
                startBtn.textContent = 'Scanner Active...';

                const ndef = new NDEFReader();
                await ndef.scan();

                ndef.addEventListener("readingerror", () => {
                    statusTitle.textContent = 'Scan Failed';
                    statusTitle.className = 'font-weight-bold mb-3 text-danger';
                    statusDesc.textContent = 'Failed to read the NFC tag. Please try tapping again.';
                });

                ndef.addEventListener("reading", async ({ message, serialNumber }) => {
                    statusTitle.textContent = 'Processing...';
                    statusTitle.className = 'font-weight-bold mb-3 text-info';
                    statusDesc.textContent = 'Recording attendance, please wait.';

                    // Call API
                    try {
                        const response = await fetch('/api/attendance/record', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Assume CSRF token is available
                            },
                            body: JSON.stringify({ serialNumber: serialNumber })
                        });

                        const data = await response.json();

                        resultCard.classList.remove('d-none');
                        if (response.ok && data.success) {
                            resultCard.classList.add('alert-success');
                            resultCard.innerHTML = `
                                <h5 class="alert-heading font-weight-bold">Success!</h5>
                                <p class="mb-0"><strong>Student:</strong> ${data.student_name}</p>
                                <p class="mb-0"><strong>Time:</strong> ${data.scanned_at}</p>
                            `;
                            statusTitle.textContent = 'Ready to Scan';
                            statusTitle.className = 'font-weight-bold mb-3 text-success';
                            statusDesc.textContent = 'Tap another card to continue.';
                        } else {
                            resultCard.classList.add('alert-danger');
                            resultCard.innerHTML = `
                                <h5 class="alert-heading font-weight-bold">Error</h5>
                                <p class="mb-0">${data.message || 'Unknown error occurred.'}</p>
                            `;
                            statusTitle.textContent = 'Ready to Scan';
                            statusTitle.className = 'font-weight-bold mb-3 text-primary';
                            statusDesc.textContent = 'Tap another card to continue.';
                        }
                    } catch (apiError) {
                        resultCard.classList.remove('d-none');
                        resultCard.classList.add('alert-danger');
                        resultCard.innerHTML = `
                            <h5 class="alert-heading font-weight-bold">Network Error</h5>
                            <p class="mb-0">Failed to connect to the server.</p>
                        `;
                        statusTitle.textContent = 'Ready to Scan';
                        statusTitle.className = 'font-weight-bold mb-3 text-primary';
                        statusDesc.textContent = 'Tap another card to continue.';
                    }
                });

            } catch (error) {
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="mdi mdi-nfc me-2"></i> Start NFC Scanner';
                
                statusTitle.textContent = 'Permission Error';
                statusTitle.className = 'font-weight-bold mb-3 text-danger';
                statusDesc.textContent = 'Scanner failed to start. Did you deny permissions? Error: ' + error.message;
            }
        });
    });
</script>
@endsection
