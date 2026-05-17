<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;
// LabelController removed; label routes moved to BarangController
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KotaController;
use App\Http\Controllers\PosController;

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// ----------------------
// Public routes
// ----------------------
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

// Demo / public pages
Route::get('/barang/simple', [BarangController::class, 'simple'])->name('barang.simple');
Route::get('/barang/datatables', [BarangController::class, 'datatables'])->name('barang.datatables');
Route::get('/kota', [KotaController::class, 'select'])->name('kota.select');

// Demo: halaman contoh cascading select untuk wilayah (provinsi -> kota -> kecamatan -> kelurahan)
Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');

// API endpoints untuk ambil data wilayah (mengambil langsung dari raw CSV di GitHub)
Route::get('/api/wilayah/provinces', [WilayahController::class, 'provinces']);
Route::get('/api/wilayah/regencies', [WilayahController::class, 'regencies']);
Route::get('/api/wilayah/districts', [WilayahController::class, 'districts']);
Route::get('/api/wilayah/villages', [WilayahController::class, 'villages']);

// ----------------------
// Authentication (keep register + email verification)
// ----------------------
Auth::routes(['verify' => true]);

// Social login (public)
Route::get('login/google', [SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('login.google.callback');

// OTP (public)
Route::get('otp', [OtpController::class, 'show'])->name('otp.show');
Route::post('otp/verify', [OtpController::class, 'verify'])->name('otp.verify');

// ----------------------
// Protected routes (requires auth, verified email, and role)
// ----------------------
Route::middleware(['auth', 'verified', 'role'])->group(function () {
    // Barcode scanner page and lookup API (register before resource to avoid resource capture)
    Route::get('/barang/scan', [BarangController::class, 'scanIndex'])->name('barang.scan.index');
    Route::get('/api/barang/scan-lookup', [BarangController::class, 'scanLookup'])->name('api.barang.scan_lookup');

    // Resources
    Route::resource('kategori', KategoriController::class);
    Route::resource('buku', BukuController::class);
    Route::resource('barang', BarangController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // PDF routes: preview, inline PDF (for preview/embed), and download
    Route::get('pdf/book-catalog/pdf', [PdfController::class, 'bookCatalogPdf'])->name('pdf.book_catalog_pdf');
    Route::get('pdf/book-catalog/download', [PdfController::class, 'bookCatalogDownload'])->name('pdf.book_catalog_download');
    Route::get('pdf/book-catalog/landscape/pdf', [PdfController::class, 'bookCatalogLandscapePdf'])->name('pdf.book_catalog_landscape_pdf');
    Route::get('pdf/book-catalog/landscape/download', [PdfController::class, 'bookCatalogLandscapeDownload'])->name('pdf.book_catalog_landscape_download');
    Route::get('pdf/book-catalog/preview/portrait', [PdfController::class, 'bookCatalogPreviewPortrait'])->name('pdf.book_catalog_preview_portrait');
    Route::get('pdf/book-catalog/preview/landscape', [PdfController::class, 'bookCatalogPreviewLandscape'])->name('pdf.book_catalog_preview_landscape');

    // Label printing for TnJ No 108 (5x8 labels) — handled by BarangController
    Route::get('/labels', [BarangController::class, 'labelsIndex'])->name('barang.labels');
    Route::post('/labels/print', [BarangController::class, 'labelsPrint'])->name('labels.print');

    // POS (Point of Sale) page and APIs
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/api/barang/lookup', [PosController::class, 'lookup']);
    Route::post('/api/pos/checkout', [PosController::class, 'checkout']);
});

