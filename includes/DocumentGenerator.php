<?php

namespace CDDU_Manager;

use Dompdf\Dompdf;
use Dompdf\Options;

class DocumentGenerator
{
    /**
     * Render a PHP template (path) with data and return HTML string
     */
    public static function renderTemplate(string $templatePath, array $data = []): string
    {
        // Provide minimal WP-like helpers when rendering outside WordPress
        $helpers = __DIR__ . '/template_helpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }

        extract($data, EXTR_OVERWRITE);
        ob_start();
        include $templatePath;
        return (string) ob_get_clean();
    }

    /**
     * Generate a PDF from a template path and data, save to $outputPath
     */
    public static function generatePdf(string $templatePath, array $data, string $outputPath): bool
    {
        $html = self::renderTemplate($templatePath, $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        return (bool) file_put_contents($outputPath, $output);
    }
}
