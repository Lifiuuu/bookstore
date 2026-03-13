<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\barang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        return view('tm5.pos');
    }

    // Lookup barang by kode (id_barang)
    public function lookup(Request $request)
    {
        $code = $request->query('code');
        if (! $code) {
            return response()->json(['error' => 'missing code'], 400);
        }

        $item = barang::where('id_barang', $code)->first();
        if (! $item) {
            return response()->json(null, 204);
        }

        return response()->json([
            'id' => $item->id_barang,
            'nama' => $item->nama,
            'harga' => (int) $item->harga,
        ]);
    }

    // Checkout: persist penjualan and penjualan_detail
    public function checkout(Request $request)
    {
        $payload = $request->all();
        $items = $payload['items'] ?? null;
        $total = $payload['total'] ?? null;

        if (! is_array($items) || count($items) === 0) {
            return response()->json(['error' => 'no items provided'], 400);
        }

        try {
            DB::beginTransaction();

            $penjualan = Penjualan::create([
                'timestamp' => now(),
                'total' => (int) $total,
            ]);

            foreach ($items as $it) {
                $penjualan->details()->create([
                    'id_barang' => $it['code'],
                    'jumlah' => (int) $it['qty'],
                    'subtotal' => (int) $it['subtotal'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'id' => $penjualan->id_penjualan]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
