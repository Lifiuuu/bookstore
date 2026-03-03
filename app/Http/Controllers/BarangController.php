<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\barang;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = barang::all();
        return view('barang.index', compact('barangs'));
    }

    public function labelsIndex()
    {
        $barangs = barang::all();
        return view('barang.labels_index', compact('barangs'));
    }

    public function labelsPrint(Request $request)
    {
        $ids = $request->input('items', []);
        
        // Start X dan Y (1-based index)
        $startX = max(1, (int) $request->input('start_x', 1));
        $startY = max(1, (int) $request->input('start_y', 1));
        
        $cols = 5;
        $rows = 8;
        $slotsPerPage = $cols * $rows; // 40 label per lembar
        
        $startX = min($cols, $startX);
        $startY = min($rows, $startY);

        // Menghitung index awal (0-based) untuk melewati label yang sudah terpakai
        $startIndex = ($startY - 1) * $cols + ($startX - 1); 

        $selected = barang::whereIn('id_barang', $ids)->orderBy('id_barang')->get();

        // --- KONFIGURASI UKURAN (Sesuaikan dengan penggaris fisikmu!) ---
        // TNJ 108 standarnya adalah 38mm (lebar) x 18mm (tinggi). 
        // Jika posisimu terbalik (portrait), tukar nilainya.
        $labelWidth = 38.0;  // mm
        $labelHeight = 18.0; // mm

        // Ukuran lembar fisik TNJ 108 (Bukan A4). Ukur lembar stiker aslinya!
        // Contoh estimasi umum lembaran TNJ: 137mm x 200mm
        $paperWidth = 210.0; // mm
        $paperHeight = 165.0; // mm
        
        // Margin dari tepi kertas ke label pertama
        $marginTop = 6.0;  // mm
        $marginLeft = 2.0; // mm
        
        // Jarak antar label
        $gapX = 3.5; // horizontal gap (mm)
        $gapY = 2.0; // vertical gap (mm)

        $pages = [];

        foreach ($selected as $idx => $item) {
            $pos = $startIndex + $idx;
            $pageNo = intdiv($pos, $slotsPerPage);
            $posInPage = $pos % $slotsPerPage;

            $col = $posInPage % $cols;
            $row = intdiv($posInPage, $cols);

            // Perhitungan koordinat Absolut untuk posisi label di PDF
            $x = $marginLeft + ($col * ($labelWidth + $gapX));
            $y = $marginTop + ($row * ($labelHeight + $gapY));

            $pages[$pageNo][] = [
                'item' => $item,
                'x' => $x,
                'y' => $y,
            ];
        }

        // Konversi milimeter ke point (pt) untuk ukuran kertas DomPDF (1 mm = 2.83465 pt)
        $mmToPt = 2.83465;
        $customPaper = array(
            0, 
            0, 
            (float)$paperWidth * $mmToPt, 
            (float)$paperHeight * $mmToPt);

        $data = [
            'pages' => $pages,
            'labelWidth' => $labelWidth,
            'labelHeight' => $labelHeight,
            'calibrate' => (bool) $request->input('calibrate', false),
        ];

        // Set ukuran kertas custom
        $pdf = Pdf::loadView('barang.labels_print', $data)->setPaper($customPaper);        
        return $pdf->stream('labels_tnj_108.pdf');
    }
}