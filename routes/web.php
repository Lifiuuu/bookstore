<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
// LabelController removed; label routes moved to BarangController
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\BarangController;

// Public route: redirect root to dashboard
Route::redirect('/', '/dashboard');

// Public dashboard (controller will hide data for guests) and protected resources
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::middleware(['auth', 'verified','role'])->group(function () {
    // Kategori
    Route::resource('kategori', KategoriController::class);
    
    // Buku
    Route::resource('buku', BukuController::class);

    //barang
    Route::resource('barang', BarangController::class);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Auth::routes(['verify' => true]);

// Google social login
Route::get('login/google', [SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('login.google.callback');

// OTP routes
Route::get('otp', [OtpController::class, 'show'])->name('otp.show');
Route::post('otp/verify', [OtpController::class, 'verify'])->name('otp.verify');

// PDF routes: preview, inline PDF (for preview/embed), and download
// preview route removed — using direct PDF routes
Route::get('pdf/book-catalog/pdf', [PdfController::class, 'bookCatalogPdf'])->name('pdf.book_catalog_pdf');
Route::get('pdf/book-catalog/download', [PdfController::class, 'bookCatalogDownload'])->name('pdf.book_catalog_download');
// Landscape (certificate-like) PDF
Route::get('pdf/book-catalog/landscape/pdf', [PdfController::class, 'bookCatalogLandscapePdf'])->name('pdf.book_catalog_landscape_pdf');
Route::get('pdf/book-catalog/landscape/download', [PdfController::class, 'bookCatalogLandscapeDownload'])->name('pdf.book_catalog_landscape_download');
// Single preview pages for portrait and landscape
Route::get('pdf/book-catalog/preview/portrait', [PdfController::class, 'bookCatalogPreviewPortrait'])->name('pdf.book_catalog_preview_portrait');
Route::get('pdf/book-catalog/preview/landscape', [PdfController::class, 'bookCatalogPreviewLandscape'])->name('pdf.book_catalog_preview_landscape');

// Label printing for TnJ No 108 (5x8 labels) — handled by BarangController
Route::get('/labels', [BarangController::class, 'labelsIndex'])->name('barang.labels');
Route::post('/labels/print', [BarangController::class, 'labelsPrint'])->name('labels.print');

