<?php

namespace App\Services;

use App\Models\NdaDocument;
use Illuminate\Support\Facades\Log;

class NdaDocumentService
{
    /**
     * Check if DomPDF is available for server-side PDF generation
     */
    public function isDomPdfAvailable(): bool
    {
        return class_exists('Barryvdh\DomPDF\Facade\Pdf');
    }

    /**
     * Generate PDF for NDA document
     * Returns HTML template if DomPDF unavailable (for browser-based generation)
     */
    public function generatePdf(NdaDocument $nda)
    {
        if ($this->isDomPdfAvailable()) {
            return $this->generateWithDomPdf($nda);
        } else {
            // Return HTML template for browser-side generation
            return $this->generateHtmlForBrowserPdf($nda);
        }
    }

    /**
     * Generate PDF using DomPDF (if available)
     */
    private function generateWithDomPdf(NdaDocument $nda)
    {
        try {
            $listing = $nda->listing;
            $signer = $nda->user;
            $seller = $listing->seller;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.nda-document', [
                'nda' => $nda,
                'listing' => $listing,
                'signer' => $signer,
                'seller' => $seller,
                'signed_date' => $nda->signed_at->format('F d, Y'),
                'signed_time' => $nda->signed_at->format('H:i:s'),
                'expires_date' => $nda->expires_at ? $nda->expires_at->format('F d, Y') : 'Never',
            ]);

            // Generate hash for document integrity
            $pdfContent = $pdf->output();
            $hash = hash('sha256', $pdfContent);
            $nda->document_hash = $hash;
            $nda->save();

            // Store PDF
            $filename = 'nda-' . $nda->listing->listing_number . '-' . $nda->id . '.pdf';
            $path = 'nda_documents/' . $filename;
            \Storage::disk('local')->put($path, $pdfContent);

            return $path;
        } catch (\Exception $e) {
            Log::error('DomPDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate HTML template for browser-based PDF generation
     */
    public function generateHtmlForBrowserPdf(NdaDocument $nda)
    {
        $listing = $nda->listing;
        $signer = $nda->user;
        $seller = $listing->seller;

        return view('pdf.nda-document', [
            'nda' => $nda,
            'listing' => $listing,
            'signer' => $signer,
            'seller' => $seller,
            'signed_date' => $nda->signed_at->format('F d, Y'),
            'signed_time' => $nda->signed_at->format('H:i:s'),
            'expires_date' => $nda->expires_at ? $nda->expires_at->format('F d, Y') : 'Never',
        ])->render();
    }

    /**
     * Generate document hash from content
     */
    public function generateDocumentHash($content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Verify document integrity
     */
    public function verifyDocumentIntegrity(NdaDocument $nda, $content): bool
    {
        if (!$nda->document_hash) {
            return false;
        }

        $currentHash = $this->generateDocumentHash($content);
        return hash_equals($nda->document_hash, $currentHash);
    }
}
