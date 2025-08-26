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
        
        $template_path = CDDU_MNGR_PATH . 'templates/contracts/contract.html.php';
        $upload_dir = wp_upload_dir();
        $output_filename = 'cddu-contract-' . $contract_id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        $output_path = $upload_dir['path'] . '/' . $output_filename;
        
        if (!self::generatePdf($template_path, $template_data, $output_path)) {
            throw new \Exception(__('Failed to generate PDF', 'wp-cddu-manager'));
        }
        
        // Store generated file path in contract meta
        update_post_meta($contract_id, 'generated_pdf_path', $output_path);
        update_post_meta($contract_id, 'generated_pdf_url', $upload_dir['url'] . '/' . $output_filename);
        
        return $upload_dir['url'] . '/' . $output_filename;
    }
    
    /**
     * Generate addendum from addendum post ID
     */
    public static function generateAddendumPdf(int $addendum_id): string {
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
            ];
        } else {
            $instructor = [
                'full_name' => __('Unknown instructor', 'wp-cddu-manager'),
            ];
        }
        
        $template_data = [
            'org' => $org,
            'instructor' => $instructor,
            'original_contract' => $contract_data,
            'addendum' => $addendum_data,
            'parent_contract_id' => $parent_contract_id,
            'addendum_id' => $addendum_id,
            'generated_date' => current_time('mysql')
        ];
        
        $template_path = CDDU_MNGR_PATH . 'templates/addendums/addendum.html.php';
        $upload_dir = wp_upload_dir();
        $output_filename = 'cddu-addendum-' . $addendum_id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        $output_path = $upload_dir['path'] . '/' . $output_filename;
        
        if (!self::generatePdf($template_path, $template_data, $output_path)) {
            throw new \Exception(__('Failed to generate addendum PDF', 'wp-cddu-manager'));
        }
        
        // Store generated file path in addendum meta
        update_post_meta($addendum_id, 'generated_pdf_path', $output_path);
        update_post_meta($addendum_id, 'generated_pdf_url', $upload_dir['url'] . '/' . $output_filename);
        
        return $upload_dir['url'] . '/' . $output_filename;
    }
}
