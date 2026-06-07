<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Barcode / QR Toko</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }
        .card {
            width: 360px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        }
        .muted {
            color: #6b7280;
            font-size: 12px;
            margin: 6px 0 0;
        }
        .barcode {
            margin-top: 18px;
        }
        #qr-code {
            display: flex;
            justify-content: center;
            margin: 18px 0 8px;
        }
        .code-value {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 10px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .card {
                border: none;
                box-shadow: none;
                width: 100%;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="card">
        <h2 style="margin: 0;">{{ $toko->nama_toko }}</h2>
        <div class="muted">Barcode / QR untuk kunjungan sales</div>

        <div id="qr-code"></div>

        <div class="code-value">{{ $toko->barcode }}</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qr-code'), {
            text: "{{ $toko->barcode }}",
            width: 180,
            height: 180,
            correctLevel: QRCode.CorrectLevel.M,
        });
    </script>
</body>
</html>