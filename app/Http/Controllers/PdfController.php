<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Controller renders static PDF views; no model imports required

class PdfController extends Controller
{

    // Preview pages for each orientation
    public function bookCatalogPreviewPortrait()
    {
        return view('pdf.preview_book_catalog_portrait');
    }

    public function bookCatalogPreviewLandscape()
    {
        return view('pdf.preview_book_catalog_landscape');
    }
    
    public function bookCatalogPdf(Request $request)
     {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.book_catalog')
                    ->setPaper('a4', 'portrait');

                // stream() by default sends inline headers; return response so iframe can display it
                return $pdf->stream('undangan-rapat.pdf');
            }

            // Fallback to HTML view
            return view('pdf.book_catalog');
        }

        /**
         * Force download of the generated PDF
         */
    public function bookCatalogDownload(Request $request)
        {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.book_catalog')
                    ->setPaper('a4', 'portrait');

                return $pdf->download('undangan-rapat.pdf');
            }

            // Fallback: render HTML view with note and a link to save page as PDF
            return view('pdf.book_catalog');
        }

        /**
         * Inline landscape PDF (certificate-like, colored)
         */
    public function bookCatalogLandscapePdf(Request $request)
        {
            $nama = $request->input('nama', 'Nama Peserta');

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.book_catalog_landscape', compact('nama'))
                    ->setPaper('a4', 'landscape');

                return $pdf->stream('sertifikat.pdf');
            }

            return view('pdf.book_catalog_landscape', compact('nama'));
        }

        /**
         * Force download landscape PDF
         */
    public function bookCatalogLandscapeDownload(Request $request)
        {
            $nama = $request->input('nama', 'Nama Peserta');

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.book_catalog_landscape', compact('nama'))
                    ->setPaper('a4', 'landscape');

                return $pdf->download('sertifikat.pdf');
            }

            return view('pdf.book_catalog_landscape', compact('nama'));
        }
}
