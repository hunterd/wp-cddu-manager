<?php
namespace CDDU_Manager\Admin;

use CDDU_Manager\Calculations;
use CDDU_Manager\DocumentGenerator;

class ContractManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_calculate_values', [$this, 'ajax_calculate_values']);
        add_action('wp_ajax_cddu_generate_contract', [$this, 'ajax_generate_contract']);
        add_action('wp_ajax_cddu_preview_contract', [$this, 'ajax_preview_contract']);
        add_action('wp_ajax_cddu_generate_pdf', [$this, 'ajax_generate_pdf']);
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_contract',
            __('Create Contract', 'wp-cddu-manager'),
            __('Create Contract', 'wp-cddu-manager'),
            'cddu_manage',
            'create-contract',
            [$this, 'render_create_contract_page']
        );
    }

    public function enqueue_scripts($hook): void {
        if ($hook !== 'cddu_contract_page_create-contract') {
            return;
        }
        
        wp_enqueue_script(
            'cddu-contract-manager',
            CDDU_MNGR_URL . 'assets/js/contract-manager.js',
            ['jquery'],
            CDDU_MNGR_VERSION,
            true
        );
        
        wp_localize_script('cddu-contract-manager', 'cddu_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cddu_contract_nonce'),
        ]);
    }

    public function render_create_contract_page(): void {
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        $instructors = get_users([
            'role' => 'cddu_instructor',
            'number' => -1
        ]);
        
        $missions = get_posts([
            'post_type' => 'cddu_mission',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        include CDDU_MNGR_PATH . 'templates/admin/create-contract-form.php';
    }

    public function ajax_calculate_values(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $data = [
            'annual_hours' => floatval($_POST['annual_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
        ];
        
        if (empty($data['start_date']) || empty($data['end_date'])) {
            wp_send_json_error(['message' => __('Start and end dates are required', 'wp-cddu-manager')]);
        }
        
        try {
            $calculations = Calculations::calculate_contract_values($data);
            wp_send_json_success($calculations);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_generate_contract(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        // Collect all form data
        $contract_data = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'instructor_user_id' => intval($_POST['instructor_user_id'] ?? 0),
            'mission_id' => intval($_POST['mission_id'] ?? 0),
            'action' => sanitize_text_field($_POST['action'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'annual_hours' => floatval($_POST['annual_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
        ];
        
        // Validate required fields
        if (empty($contract_data['organization_id']) || empty($contract_data['instructor_user_id'])) {
            wp_send_json_error(['message' => __('Organization and instructor are required', 'wp-cddu-manager')]);
        }
        
        try {
            // Get instructor user data for the contract title
            $instructor_user = get_userdata($contract_data['instructor_user_id']);
            $instructor_name = $instructor_user ? $instructor_user->display_name : __('Unknown Instructor', 'wp-cddu-manager');
            
            // Create contract post
            $contract_id = wp_insert_post([
                'post_type' => 'cddu_contract',
                'post_title' => sprintf(
                    __('Contract %s - %s', 'wp-cddu-manager'),
                    $instructor_name,
                    date('Y-m-d')
                ),
                'post_status' => 'draft',
                'meta_input' => [
                    'organization_id' => $contract_data['organization_id'],
                    'instructor_user_id' => $contract_data['instructor_user_id'],
                    'mission_id' => $contract_data['mission_id'],
                    'contract_data' => maybe_serialize($contract_data),
                    'calculations' => maybe_serialize(Calculations::calculate_contract_values($contract_data)),
                    'status' => 'draft',
                    'created_date' => current_time('mysql')
                ]
            ]);
            
            if (is_wp_error($contract_id)) {
                wp_send_json_error(['message' => $contract_id->get_error_message()]);
            }
            
            wp_send_json_success([
                'contract_id' => $contract_id,
                'edit_url' => admin_url('post.php?post=' . $contract_id . '&action=edit'),
                'message' => __('Contract created successfully', 'wp-cddu-manager')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function ajax_preview_contract(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        // Collect form data for preview
        $preview_data = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'instructor_user_id' => intval($_POST['instructor_user_id'] ?? 0),
            'action' => sanitize_text_field($_POST['action'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'annual_hours' => floatval($_POST['annual_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
        ];
        
        try {
            // Get organization data
            if ($preview_data['organization_id']) {
                $org_meta = get_post_meta($preview_data['organization_id'], 'org', true);
                $org = maybe_unserialize($org_meta) ?: [];
                $org['name'] = $org['name'] ?? get_the_title($preview_data['organization_id']);
            } else {
                $org = ['name' => 'Organization Name', 'address' => 'Organization Address'];
            }
            
            // Get instructor data
            if ($preview_data['instructor_user_id']) {
                $instructor_user = get_userdata($preview_data['instructor_user_id']);
                if ($instructor_user) {
                    $instructor = [
                        'full_name' => $instructor_user->display_name,
                        'email' => $instructor_user->user_email,
                        'address' => get_user_meta($instructor_user->ID, 'address', true) ?: 'Instructor Address'
                    ];
                } else {
                    $instructor = ['full_name' => 'Instructor Name', 'address' => 'Instructor Address'];
                }
            } else {
                $instructor = ['full_name' => 'Instructor Name', 'address' => 'Instructor Address'];
            }
            
            // Calculate values
            $calculations = Calculations::calculate_contract_values($preview_data);
            
            $template_data = [
                'org' => $org,
                'instructor' => $instructor,
                'mission' => $preview_data,
                'calc' => $calculations,
                'preview' => true
            ];
            
            $template_path = CDDU_MNGR_PATH . 'templates/contracts/contract.html.php';
            $html = DocumentGenerator::renderTemplate($template_path, $template_data);
            
            wp_send_json_success(['html' => $html]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function ajax_generate_pdf(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $contract_id = intval($_POST['contract_id'] ?? 0);
        if (!$contract_id) {
            wp_send_json_error(['message' => __('Invalid contract ID', 'wp-cddu-manager')]);
        }
        
        try {
            $pdf_url = DocumentGenerator::generateContractPdf($contract_id);
            wp_send_json_success([
                'pdf_url' => $pdf_url,
                'message' => __('PDF generated successfully', 'wp-cddu-manager')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
