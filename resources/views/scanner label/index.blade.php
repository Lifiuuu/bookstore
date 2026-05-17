@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Scanner Label — Pembaca Barcode</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div id="reader" style="width:420px;max-width:100%;min-height:300px;margin:0 auto;background:#000;display:flex;align-items:center;justify-content:center;color:#fff">Memulai kamera...</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Keranjang Scan</h5>
                <div class="text-muted small">Barang yang sudah terscan tampil langsung di tabel ini</div>
            </div>

            <div id="scan-status" class="alert alert-light border py-2 px-3 mb-3 text-muted">
                Menunggu hasil scan...
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="empty-cart-row">
                            <td colspan="6" class="text-center text-muted">Belum ada barang yang dipindai.</td>
                        </tr>
                    </tbody>
                </table>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <strong>Total: Rp <span id="grand-total">0</span></strong>
                <small class="text-muted"><span id="item-count">0</span> item</small>
            </div>

            <hr />
            <label>Masukkan manual (jika kamera gagal):</label>
            <div class="input-group mb-2">
                <input id="manual-code" class="form-control" placeholder="Masukkan kode" />
                <button id="manual-lookup" class="btn btn-primary">Cari</button>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-2">
                <button id="restart-scanner" class="btn btn-secondary">Mulai ulang scanner</button>
                <button id="clear-cart" class="btn btn-outline-danger">Kosongkan</button>
                <button id="print-receipt" class="btn btn-success" disabled>Cetak Struk</button>
            </div>
        </div>
    </div>

    <!-- Audio: place your attached beep mp3 at public/audio/beep.mp3 -->
    <audio id="beep" preload="auto">
        <source src="/audio/beep.mp3" type="audio/mpeg">
    </audio>
</div>

@push('scripts')
<!-- html5-qrcode from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    const scanStatus = document.getElementById('scan-status');
    const beep = document.getElementById('beep');
    const cartBody = document.getElementById('cart-body');
    const grandTotalEl = document.getElementById('grand-total');
    const itemCountEl = document.getElementById('item-count');
    const printReceiptButton = document.getElementById('print-receipt');
    const clearCartButton = document.getElementById('clear-cart');

    let html5QrCode;
    const readerId = 'reader';
    const cart = new Map();
    const currencyFormatter = new Intl.NumberFormat('id-ID');
    const scanCooldownMs = 3000;
    let lastScanCode = '';
    let lastScanAt = 0;

    // create camera selector UI
    const controlsCard = document.querySelector('.card-body');
    const cameraSelectWrap = document.createElement('div');
    cameraSelectWrap.className = 'mb-2';
    cameraSelectWrap.innerHTML = `
        <label for="camera-select">Pilih Kamera:</label>
        <select id="camera-select" class="form-control"></select>
    `;
    controlsCard.insertBefore(cameraSelectWrap, controlsCard.firstChild);
    const cameraSelect = document.getElementById('camera-select');

    function money(value) {
        return currencyFormatter.format(Number(value) || 0);
    }

    function updateSummary() {
        let total = 0;
        let items = 0;

        cart.forEach((item) => {
            total += item.subtotal;
            items += item.qty;
        });

        grandTotalEl.textContent = money(total);
        itemCountEl.textContent = items;
        printReceiptButton.disabled = cart.size === 0;
    }

    function renderCart() {
        if (cart.size === 0) {
            cartBody.innerHTML = `
                <tr id="empty-cart-row">
                    <td colspan="6" class="text-center text-muted">Belum ada barang yang dipindai.</td>
                </tr>
            `;
            updateSummary();
            return;
        }

        let rows = '';
        cart.forEach((item) => {
            rows += `
                <tr data-code="${escapeHtml(item.code)}">
                    <td>${escapeHtml(item.code)}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td class="text-end">Rp ${money(item.price)}</td>
                    <td class="text-center">
                        <input type="number" min="1" class="form-control form-control-sm cart-qty" value="${item.qty}" style="max-width: 80px; margin: 0 auto;">
                    </td>
                    <td class="text-end row-subtotal">Rp ${money(item.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item">Hapus</button>
                    </td>
                </tr>
            `;
        });

        cartBody.innerHTML = rows;
        updateSummary();
    }

    function addToCart(data) {
        const code = String(data.id_barang ?? '').trim();
        if (!code) {
            return;
        }

        const price = Number(data.harga) || 0;
        const existing = cart.get(code);

        if (existing) {
            existing.qty += 1;
            existing.subtotal = existing.qty * existing.price;
        } else {
            cart.set(code, {
                code: code,
                name: data.nama || '-',
                price: price,
                qty: 1,
                subtotal: price,
            });
        }

        try { beep.currentTime = 0; beep.play(); } catch (e) {}
        renderCart();
    }

    function lookupCode(code){
        const normalizedCode = String(code || '').trim();
        if (!normalizedCode) {
            return;
        }

        const now = Date.now();
        if (normalizedCode === lastScanCode && (now - lastScanAt) < scanCooldownMs) {
            return;
        }
        lastScanCode = normalizedCode;
        lastScanAt = now;

        scanStatus.className = 'alert alert-light border py-2 px-3 mb-3 text-muted';
        scanStatus.textContent = 'Memeriksa kode...';
        fetch(`/api/barang/scan-lookup?code=${encodeURIComponent(normalizedCode)}`)
            .then(async r => {
                const ct = r.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const j = await r.json();
                    return { ok: r.ok, body: j };
                }
                // not JSON -> read text (likely an HTML error page). Return text so we can show useful info
                const t = await r.text();
                return { ok: r.ok, body: t, text: true, status: r.status };
            })
            .then(res => {
                if (res.ok && !res.text) {
                    addToCart(res.body);
                    scanStatus.className = 'alert alert-success py-2 px-3 mb-3';
                    scanStatus.innerHTML = `Ditambahkan: <strong>${escapeHtml(res.body.nama || '-')}</strong>`;
                    return;
                }

                // handle non-JSON or non-ok responses
                if (res.text) {
                    // server returned HTML or plain text (e.g., an error page)
                    const snippet = (res.body || '').slice(0, 300);
                    scanStatus.className = 'alert alert-danger py-2 px-3 mb-3';
                    scanStatus.innerHTML = `Server returned non-JSON (status ${res.status}): <pre class="mb-0" style="white-space:pre-wrap">${escapeHtml(snippet)}</pre>`;
                } else if (!res.ok && res.body && res.body.error) {
                    scanStatus.className = 'alert alert-danger py-2 px-3 mb-3';
                    scanStatus.textContent = res.body.error;
                } else {
                    scanStatus.className = 'alert alert-warning py-2 px-3 mb-3';
                    scanStatus.textContent = 'Tidak ditemukan';
                }
            }).catch(err => {
                console.error('lookupCode error', err);
                scanStatus.className = 'alert alert-danger py-2 px-3 mb-3';
                scanStatus.innerHTML = `Kesalahan: ${escapeHtml(err.message || String(err))}`;
            });
    }

    // small helper to avoid inserting raw HTML from server into the page
    function escapeHtml(s){
        if (!s) return '';
        return String(s).replace(/[&<>"]/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]); });
    }

function startScanner(){
    scanStatus.className = 'alert alert-light border py-2 px-3 mb-3 text-muted';
    scanStatus.textContent = 'Menunggu hasil scan...';
        html5QrCode = new Html5Qrcode(readerId);
        
        // First, try to enumerate cameras
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                // populate camera select
                cameraSelect.innerHTML = '';
                cameras.forEach((c, idx) => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.text = c.label || ('Camera ' + (idx+1));
                    cameraSelect.appendChild(opt);
                });

                const chosenId = cameraSelect.value || cameras[0].id;
                startWithDeviceId(chosenId);
            } else {
                startWithFacingMode();
            }
        }).catch(err => {
            if (err && err.name === 'NotAllowedError') {
                document.getElementById(readerId).innerText = 'Izin kamera ditolak. Mohon izinkan akses kamera di browser (akses via localhost/127.0.0.1).';
                return;
            }
            startWithFacingMode();
        });
    }

    function startWithDeviceId(deviceId) {
        const config = { fps: 10, qrbox: { width: 300, height: 150 } };
        html5QrCode.start(
            { deviceId: { exact: deviceId } },
            config,
            (decodedText, decodedResult) => lookupCode(decodedText),
            (errorMessage) => {}
        ).catch(err => {
            document.getElementById(readerId).innerText = 'Gagal memulai kamera: ' + err;
        });
    }

    function startWithFacingMode() {
        const config = { fps: 10, qrbox: { width: 300, height: 150 } };
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => lookupCode(decodedText),
            (errorMessage) => {}
        ).catch(err => {
            document.getElementById(readerId).innerText = 'Gagal memulai kamera (fallback): ' + err;
        });
    }

    // === DAFTAR EVENT LISTENERS DITARUH DI SINI ===

    // 1. Event listener ganti kamera
    cameraSelect.addEventListener('change', (e) => {
        const newDeviceId = e.target.value;
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                startWithDeviceId(newDeviceId);
            }).catch(err => {
                console.error("Gagal menghentikan kamera saat ganti perangkat:", err);
            });
        }
    });

    // 2. Event listener manual lookup
    document.getElementById('manual-lookup').addEventListener('click', () => {
        const code = document.getElementById('manual-code').value.trim();
        if (!code) return;
        lookupCode(code);
    });

    // 3. Event listener restart scanner
    document.getElementById('restart-scanner').addEventListener('click', () => {
        try { html5QrCode && html5QrCode.stop().catch(()=>{}); } catch(e){}
        startScanner();
    });

    clearCartButton.addEventListener('click', () => {
        cart.clear();
        renderCart();
    });

    cartBody.addEventListener('change', (event) => {
        const target = event.target;
        if (!target.classList.contains('cart-qty')) {
            return;
        }

        const row = target.closest('tr');
        const code = row ? row.getAttribute('data-code') : null;
        const item = code ? cart.get(code) : null;
        const qty = Math.max(1, parseInt(target.value, 10) || 1);

        if (!item) {
            return;
        }

        item.qty = qty;
        item.subtotal = item.qty * item.price;
        renderCart();
    });

    cartBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!target.classList.contains('btn-remove-item')) {
            return;
        }

        const row = target.closest('tr');
        const code = row ? row.getAttribute('data-code') : null;
        if (code) {
            cart.delete(code);
            renderCart();
        }
    });

    printReceiptButton.addEventListener('click', () => {
        if (cart.size === 0) {
            return;
        }

        const receiptWindow = window.open('', '_blank', 'width=420,height=720');
        if (!receiptWindow) {
            alert('Popup diblokir browser. Izinkan popup untuk mencetak struk.');
            return;
        }

        let itemsHtml = '';
        let total = 0;

        cart.forEach((item) => {
            itemsHtml += `
                <tr>
                    <td>${escapeHtml(item.name)}</td>
                    <td style="text-align:center;">${item.qty}</td>
                    <td style="text-align:right;">Rp ${money(item.subtotal)}</td>
                </tr>
            `;
            total += item.subtotal;
        });

        receiptWindow.document.write(`
            <html>
            <head>
                <title>Struk Penjualan</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 16px; color: #111; }
                    h2, p { margin: 0 0 8px; }
                    .meta { font-size: 12px; color: #555; margin-bottom: 12px; }
                    table { width: 100%; border-collapse: collapse; font-size: 12px; }
                    th, td { border-bottom: 1px solid #ddd; padding: 6px 0; vertical-align: top; }
                    th { text-align: left; }
                    .summary { margin-top: 12px; font-size: 13px; }
                    .summary strong { display: inline-block; min-width: 110px; }
                </style>
            </head>
            <body>
                <h2>Struk Belanja</h2>
                <div class="meta">${new Date().toLocaleString('id-ID')}</div>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
                <div class="summary">
                    <p><strong>Total item</strong>: ${itemCountEl.textContent}</p>
                    <p><strong>Total bayar</strong>: Rp ${money(total)}</p>
                </div>
            </body>
            </html>
        `);
        receiptWindow.document.close();
        receiptWindow.focus();
        receiptWindow.onload = () => {
            receiptWindow.print();
        };
    });

    // auto-start on page load
    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', () => {
            startScanner();
        });
    } else {
        startScanner();
    }
</script>
@endpush

@endsection
