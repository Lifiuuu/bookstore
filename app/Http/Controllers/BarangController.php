<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = barang::all();
        return view('barang.index', compact('barangs'));
    }

    /**
     * Show simple barang demo page
     */
    public function simple()
    {
        return view('tm4.barang_simple');
    }

    /**
     * Show datatables barang demo page
     */
    public function datatables()
    {
        return view('tm4.barang_datatables');
    }

    /**
     * Show form to create a new barang
     */
    public function create()
    {
        return view('barang.create');
    }

    /**
     * Store a newly created barang
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric',
        ]);

        barang::create($data);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function labelsIndex()
    {
        $barangs = barang::all();
        return view('barang.labels_index', compact('barangs'));
    }

    /**
     * Show form to edit an existing barang
     */
    public function edit($id)
    {
        $barang = barang::where('id_barang', $id)->firstOrFail();
        return view('barang.edit', compact('barang'));
    }

    /**
     * Update an existing barang
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric',
        ]);

        $barang = barang::where('id_barang', $id)->firstOrFail();
        $barang->update($data);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Delete a barang
     */
    public function destroy($id)
    {
        $barang = barang::where('id_barang', $id)->firstOrFail();
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
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
        $marginTop = 5.5;  // mm
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

            // Generate barcode PNG (base64) for this item's id (if library available)
            $barcodeDataUri = null;
            try {
                $identifier = $item->id_barang ?? $item->id ?? '';
                if (!empty($identifier)) {
                    $generator = new BarcodeGeneratorPNG();
                    $png = $generator->getBarcode($identifier, $generator::TYPE_CODE_128);
                    $barcodeDataUri = 'data:image/png;base64,' . base64_encode($png);
                }
            } catch (\Throwable $e) {
                // If barcode generation fails (library not installed), continue without barcode
                $barcodeDataUri = null;
            }

            $pages[$pageNo][] = [
                'item' => $item,
                'x' => $x,
                'y' => $y,
                'barcode' => $barcodeDataUri,
                'id_value' => $item->id_barang ?? $item->id ?? null,
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

    /**
     * Show barcode scanner page (reads barcode from label)
     */
    public function scanIndex()
    {
        return view('scanner label.index');
    }

    /**
     * Lookup barang by scanned code and return JSON
     */
    public function scanLookup(Request $request)
    {
        $code = $request->query('code');
        if (empty($code)) {
            return response()->json(['error' => 'Missing code'], 400);
        }

        // Try matching by primary key `id_barang` safely.
        // Avoid querying a non-existent `id` column which can cause SQL errors on some DBs.
        $item = barang::where('id_barang', $code)->first();
        if (!$item && is_numeric($code)) {
            // Use Eloquent find which respects the model's primaryKey (`id_barang`).
            $item = barang::find($code);
        }

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json([
            'id_barang' => $item->id_barang ?? $item->id,
            'nama' => $item->nama ?? $item->nama_barang ?? null,
            'harga' => $item->harga ?? 0,
        ]);
    }
}