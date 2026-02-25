<?php

namespace App\Helpers;

class PdfHelper
{
    /**
     * Check if PDF generation is available
     */
    public static function isPdfGenerationAvailable(): bool
    {
        return class_exists('Barryvdh\DomPDF\Facade\Pdf');
    }

    /**
     * Get PDF generation method
     */
    public static function getPdfGenerationMethod(): string
    {
        return self::isPdfGenerationAvailable() ? 'server' : 'browser';
    }
}
