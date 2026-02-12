<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\View\View;

class PageController extends Controller
{

    /**
     * Tampilkan halaman welcome
     */
    public function welcome(): View
    {
        return view('welcome');
    }

    /**
     * Tampilkan dashboard
     */
    public function dashboard(): View
    {
        $total_kategori = Kategori::count();
        $total_buku = Buku::count();
        $latest_buku_count = Buku::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count();
        $kategoris = Kategori::all();
        $bukus = Buku::with('kategori')->latest()->take(5)->get();

        return view('dashboard', compact('total_kategori', 'total_buku', 'latest_buku_count', 'kategoris', 'bukus'));
    }
}
