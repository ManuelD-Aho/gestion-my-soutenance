<?php

namespace App\Services;

use Spatie\LaravelPdf\Facades\Pdf;

class PdfGenerationService
{
    public function generatePdf(string $view, array $data, string $filename): string
    {
        $path = storage_path('app/private/documents/' . $filename);
        Pdf::view($view, $data)->save($path);
        return $path;
    }
}
