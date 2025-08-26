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
    
    /**
     * Generate CDDU contract from contract post ID
     */
    public static function generateContractPdf(int $contract_id): string {
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $calculations = get_post_meta($contract_id, 'calculations', true);
        
        if (!$contract_data || !$calculations) {
            throw new \Exception(__('Contract data not found', 'wp-cddu-manager'));
        }
        
        $contract_data = maybe_unserialize($contract_data);
        $calculations = maybe_unserialize($calculations);
        
        // Get organization data
        $org_id = $contract_data['organization_id'];
        $org_meta = get_post_meta($org_id, 'org', true);
        $org = maybe_unserialize($org_meta) ?: [];
        $org['name'] = $org['name'] ?? get_the_title($org_id);
        
        // Get instructor data (now from user instead of post)
        $instructor_user_id = $contract_data['instructor_user_id'];
        $instructor_user = get_userdata($instructor_user_id);
        
        if ($instructor_user) {
            $instructor = [
                'full_name' => $instructor_user->display_name,
                'first_name' => $instructor_user->first_name,
                'last_name' => $instructor_user->last_name,
                'email' => $instructor_user->user_email,
                'address' => get_user_meta($instructor_user_id, 'address', true),
                'phone' => get_user_meta($instructor_user_id, 'phone', true),
                'birth_date' => get_user_meta($instructor_user_id, 'birth_date', true),
                'birth_place' => get_user_meta($instructor_user_id, 'birth_place', true),
                'ssn' => get_user_meta($instructor_user_id, 'ssn', true),
            ];
        } else {
            $instructor = [
                'full_name' => __('Unknown instructor', 'wp-cddu-manager'),
            ];
        }
        
        // Mission data from contract
        $mission = [
            'action' => $contract_data['action'],
            'location' => $contract_data['location'],
            'annual_hours' => $contract_data['annual_hours'],
            'hourly_rate' => $contract_data['hourly_rate'],
            'start_date' => $contract_data['start_date'],
            'end_date' => $contract_data['end_date'],
        ];
        
        $template_data = [
            'org' => $org,
            'instructor' => $instructor,
            'mission' => $mission,
            'calc' => $calculations,
            'contract_id' => $contract_id,
            'generated_date' => current_time('mysql')
        ];
        
        $upload_dir = wp_upload_dir();
        $output_filename = 'cddu-contract-' . $contract_id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        $output_path = $upload_dir['path'] . '/' . $output_filename;
        
        // Use custom content if available
        if (!empty($contract_data['contract_content'])) {
            $html = self::processContractContent($contract_data['contract_content'], $template_data);
            
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            $success = (bool) file_put_contents($output_path, $output);
        } else {
            // Use default template
            $template_path = CDDU_MNGR_PATH . 'templates/contracts/contract.html.php';
            $success = self::generatePdf($template_path, $template_data, $output_path);
        }
        
        if (!$success) {
            throw new \Exception(__('Failed to generate PDF', 'wp-cddu-manager'));
        }
        
        // Store generated file path in contract meta
        update_post_meta($contract_id, 'generated_pdf_path', $output_path);
        update_post_meta($contract_id, 'generated_pdf_url', $upload_dir['url'] . '/' . $output_filename);
        
        return $upload_dir['url'] . '/' . $output_filename;
    }
    
    /**
     * Generate addendum from addendum post ID with template selection
     */
    public static function generateAddendumPdf(int $addendum_id, string $template_name = 'addendum'): string {
        $addendum_data = get_post_meta($addendum_id, 'addendum_data', true);
        $parent_contract_id = get_post_meta($addendum_id, 'parent_contract_id', true);
        
        if (!$addendum_data || !$parent_contract_id) {
            throw new \Exception(__('Addendum data not found', 'wp-cddu-manager'));
        }
        
        $addendum_data = maybe_unserialize($addendum_data);
        
        // Get original contract data
        $contract_data = get_post_meta($parent_contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        // Get organization and instructor data from original contract
        $org_id = $contract_data['organization_id'];
        $org_meta = get_post_meta($org_id, 'org', true);
        $org = maybe_unserialize($org_meta) ?: [];
        
        $instructor_user_id = $contract_data['instructor_user_id'];
        $instructor_user = get_userdata($instructor_user_id);
        
        if ($instructor_user) {
            $instructor = [
                'full_name' => $instructor_user->display_name,
                'first_name' => $instructor_user->first_name,
                'last_name' => $instructor_user->last_name,
                'email' => $instructor_user->user_email,
                'address' => get_user_meta($instructor_user_id, 'address', true),
                'phone' => get_user_meta($instructor_user_id, 'phone', true),
                'civility' => get_user_meta($instructor_user_id, 'civility', true) ?: 'Monsieur',
                'birth_date' => get_user_meta($instructor_user_id, 'birth_date', true),
                'birth_place' => get_user_meta($instructor_user_id, 'birth_place', true),
                'social_security_number' => get_user_meta($instructor_user_id, 'social_security_number', true),
                'job_title' => get_user_meta($instructor_user_id, 'job_title', true) ?: 'Formateur',
                'classification_level' => get_user_meta($instructor_user_id, 'classification_level', true) ?: 'Palier 9',
                'coefficient' => get_user_meta($instructor_user_id, 'coefficient', true) ?: '200',
            ];
        } else {
            $instructor = [
                'full_name' => __('Unknown instructor', 'wp-cddu-manager'),
            ];
        }
        
        // Add calculation data if available
        $calculations = get_post_meta($addendum_id, 'calculations_data', true);
        if (!$calculations) {
            $calculations = get_post_meta($parent_contract_id, 'calculations_data', true);
        }
        $calculations = maybe_unserialize($calculations) ?: [];
        
        // Add addendum number
        $addendum_number = get_post_meta($addendum_id, 'addendum_number', true) ?: '1';
        
        $template_data = [
            'organization' => $org,
            'instructor' => $instructor,
            'contract' => $contract_data,
            'addendum' => $addendum_data,
            'calculations' => $calculations,
            'addendum_number' => $addendum_number,
            'parent_contract_id' => $parent_contract_id,
            'addendum_id' => $addendum_id,
            'generated_date' => current_time('mysql')
        ];
        
        // Select template based on organization or parameter
        $template_filename = $template_name . '.html.php';
        $template_path = CDDU_MNGR_PATH . 'templates/addendums/' . $template_filename;
        
        // Fallback to default template if specified template doesn't exist
        if (!file_exists($template_path)) {
            $template_path = CDDU_MNGR_PATH . 'templates/addendums/addendum.html.php';
        }
        
        $upload_dir = wp_upload_dir();
        $output_filename = 'cddu-addendum-' . $addendum_id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        $output_path = $upload_dir['path'] . '/' . $output_filename;
        
        if (!self::generatePdf($template_path, $template_data, $output_path)) {
            throw new \Exception(__('Failed to generate addendum PDF', 'wp-cddu-manager'));
        }
        
        // Store generated file path in addendum meta
        update_post_meta($addendum_id, 'generated_pdf_path', $output_path);
        update_post_meta($addendum_id, 'generated_pdf_url', $upload_dir['url'] . '/' . $output_filename);
        update_post_meta($addendum_id, 'template_used', $template_name);
        
        return $upload_dir['url'] . '/' . $output_filename;
    }
    
    /**
     * Process custom contract content with variable interpolation
     */
    public static function processContractContent(string $content, array $data): string {
        // Add current date
        $data['current_date'] = date('d/m/Y');
        $data['contract_date'] = date('d/m/Y');
        
        // Flatten data structure for easier variable replacement
        $variables = self::flattenVariableData($data);
        
        // Replace variables in content
        $processed_content = $content;
        foreach ($variables as $key => $value) {
            $pattern = '/\{\{' . preg_quote($key, '/') . '\}\}/';
            $processed_content = preg_replace($pattern, self::formatVariableValue($value), $processed_content);
        }
        
        // Wrap in basic HTML structure for PDF generation
        return self::wrapContentForPdf($processed_content);
    }
    
    /**
     * Flatten nested array data into dot notation for variables
     */
    private static function flattenVariableData(array $data, string $prefix = ''): array {
        $result = [];
        
        foreach ($data as $key => $value) {
            $newKey = $prefix . $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flattenVariableData($value, $newKey . '.'));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Format variable values for display
     */
    private static function formatVariableValue($value): string {
        if (is_null($value) || $value === '') {
            return '';
        }
        
        if (is_numeric($value)) {
            // Format numbers with 2 decimal places if they have decimals
            if (floor($value) != $value) {
                return number_format((float)$value, 2, ',', ' ');
            }
            return (string)$value;
        }
        
        return (string)$value;
    }
    
    /**
     * Wrap processed content in HTML structure for PDF
     */
    private static function wrapContentForPdf(string $content): string {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat CDDU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 30px;
            color: #000;
        }
        h2 {
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #000;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        ul, ol {
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
        p {
            margin-bottom: 10px;
        }
        strong {
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    ' . $content . '
</body>
</html>';
    }
}
