<?php

declare(strict_types=1);

namespace Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private function makeDompdf(): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', ROOT_PATH . '/views/pdf');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        return $dompdf;
    }

    /**
     * Generate a PDF from an HTML template view.
     *
     * @param string $template  Template name under views/pdf/ (without .php)
     * @param array  $data      Variables to pass into the template
     * @return string           Raw PDF binary string
     */
    public function generate(string $template, array $data = []): string
    {
        $templatePath = ROOT_PATH . '/views/pdf/' . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("PDF template not found: {$template}");
        }

        // Render HTML from template
        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;
        $html = ob_get_clean();

        $dompdf = $this->makeDompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Stream a PDF directly to the browser.
     */
    public function stream(string $template, array $data = [], string $filename = 'document.pdf'): never
    {
        $pdf = $this->generate($template, $data);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    /**
     * Resolve which PDF template to use for a given request type.
     */
    public function templateFor(string $requestType): string
    {
        return match ($requestType) {
            'barangay_clearance'       => 'clearance',
            'certificate_of_residency' => 'residency',
            'certificate_of_indigency' => 'indigency',
            'cedula'                   => 'cedula',
            'barangay_id'              => 'barangay-id',
            default => throw new \InvalidArgumentException("No PDF template for type: {$requestType}"),
        };
    }

    /**
     * Build document filename for a request.
     */
    public function filenameFor(string $requestType, string $residentName, string $date): string
    {
        $type = str_replace('_', '-', $requestType);
        $name = strtolower(str_replace(' ', '-', $residentName));
        return "{$type}_{$name}_{$date}.pdf";
    }
}
