<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // Tambahan untuk fungsi random string
use App\Models\LokasiToko;
use Picqer\Barcode\BarcodeGeneratorPNG;

class kunjungantoko extends Controller
{
    public function index()
    {
        // 1. Generate Barcode Otomatis (Total 8 Karakter)
        $newBarcode = '';
        do {
            // Format: "TK" + 6 karakter acak (huruf/angka)
            $newBarcode = 'TK' . strtoupper(Str::random(6));
        } while (LokasiToko::where('barcode', $newBarcode)->exists());

        // 2. Tarik data untuk tabel
        $tokoList = LokasiToko::orderBy('nama_toko')->get();

        return view('kunjungan-toko.pembuat-qr-toko', [
            'tokoList' => $tokoList,
            'editingToko' => null,
            'newBarcode' => $newBarcode, // Lempar variabel barcode baru ke view
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'size:8', 'alpha_num', 'unique:lokasi_toko,barcode'],
            'nama_toko' => ['required', 'string', 'max:50'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $data['barcode'] = strtoupper(trim($data['barcode']));

        LokasiToko::create($data);

        return redirect()->route('kunjungan_toko.index')->with('success', 'Data toko berhasil ditambahkan.');
    }

    public function edit(LokasiToko $toko)
    {
        $tokoList = LokasiToko::orderBy('nama_toko')->get();

        return view('kunjungan-toko.pembuat-qr-toko', [
            'tokoList' => $tokoList,
            'editingToko' => $toko,
            'newBarcode' => null, // Lempar null karena sedang mode edit, bukan tambah
        ]);
    }

    public function update(Request $request, LokasiToko $toko)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'size:8', 'alpha_num', Rule::unique('lokasi_toko', 'barcode')->ignore($toko->barcode, 'barcode')],
            'nama_toko' => ['required', 'string', 'max:50'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $toko->update([
            'nama_toko' => $data['nama_toko'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'],
        ]);

        return redirect()->route('kunjungan_toko.index')->with('success', 'Data toko berhasil diperbarui.');
    }

    public function destroy(LokasiToko $toko)
    {
        $toko->delete();

        return redirect()->route('kunjungan_toko.index')->with('success', 'Data toko berhasil dihapus.');
    }

    public function scanner()
    {
        return view('kunjungan-toko.scanner-geolocation');
    }

    public function lookup(string $barcode)
    {
        $toko = LokasiToko::find(strtoupper(trim($barcode)));

        if (!$toko) {
            return response()->json([
                'message' => 'Barcode toko tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'barcode' => $toko->barcode,
            'nama_toko' => $toko->nama_toko,
            'latitude' => $toko->latitude,
            'longitude' => $toko->longitude,
            'accuracy' => $toko->accuracy,
        ]);
    }

    public function validateVisit(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'size:8', 'alpha_num'],
            'sales_latitude' => ['required', 'numeric'],
            'sales_longitude' => ['required', 'numeric'],
            'sales_accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $toko = LokasiToko::find(strtoupper(trim($data['barcode'])));

        if (!$toko) {
            return response()->json([
                'message' => 'Data toko tidak ditemukan.',
            ], 404);
        }

        $distanceMeters = $this->haversineMeters(
            (float) $toko->latitude,
            (float) $toko->longitude,
            (float) $data['sales_latitude'],
            (float) $data['sales_longitude']
        );

        $thresholdBase = 300.0;
        $effectiveThreshold = $thresholdBase + (float) $toko->accuracy + (float) $data['sales_accuracy'];
        $accepted = $distanceMeters <= $effectiveThreshold;

        return response()->json([
            'message' => $accepted ? 'Kunjungan diterima.' : 'Kunjungan ditolak.',
            'status' => $accepted ? 'diterima' : 'ditolak',
            'toko' => [
                'barcode' => $toko->barcode,
                'nama_toko' => $toko->nama_toko,
                'latitude' => (float) $toko->latitude,
                'longitude' => (float) $toko->longitude,
                'accuracy' => (float) $toko->accuracy,
            ],
            'sales' => [
                'latitude' => (float) $data['sales_latitude'],
                'longitude' => (float) $data['sales_longitude'],
                'accuracy' => (float) $data['sales_accuracy'],
            ],
            'distance_m' => round($distanceMeters, 2),
            'threshold_base_m' => $thresholdBase,
            'threshold_effective_m' => round($effectiveThreshold, 2),
            'accepted' => $accepted,
        ]);
    }

    public function print(LokasiToko $toko)
    {
        $barcodeImage = $this->generateBarcodeImage($toko->barcode);

        return view('kunjungan-toko.print', compact('toko', 'barcodeImage'));
    }

    private function haversineMeters(float $latFrom, float $lonFrom, float $latTo, float $lonTo): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lonFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lonTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function generateBarcodeImage(string $barcode): string
    {
        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 60);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}