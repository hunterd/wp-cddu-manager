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
        add_action('wp_ajax_cddu_generate_addendum', [$this, 'ajax_generate_addendum']);
        add_action('wp_ajax_cddu_preview_addendum', [$this, 'ajax_preview_addendum']);
        add_action('wp_ajax_cddu_get_instructor_data', [$this, 'ajax_get_instructor_data']);
        add_action('wp_ajax_cddu_get_contract_data', [$this, 'ajax_get_contract_data']);
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
        
        add_submenu_page(
            'edit.php?post_type=cddu_contract',
            __('Create Addendum', 'wp-cddu-manager'),
            __('Create Addendum', 'wp-cddu-manager'),
            'cddu_manage',
            'create-addendum',
            [$this, 'render_create_addendum_page']
        );
    }

    public function enqueue_scripts($hook): void {
        if ($hook !== 'cddu_contract_page_create-contract' && $hook !== 'cddu_contract_page_create-addendum') {
            return;
        }
        
        if ($hook === 'cddu_contract_page_create-addendum') {
            wp_enqueue_style(
                'cddu-addendum-form',
                CDDU_MNGR_URL . 'assets/css/addendum-form.css',
                [],
                CDDU_MNGR_VERSION
            );
            
            wp_enqueue_script(
                'cddu-addendum-manager',
                CDDU_MNGR_URL . 'assets/js/addendum-manager.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
        } else {
            wp_enqueue_script(
                'cddu-contract-manager',
                CDDU_MNGR_URL . 'assets/js/contract-manager.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
        }
        
        wp_localize_script($hook === 'cddu_contract_page_create-addendum' ? 'cddu-addendum-manager' : 'cddu-contract-manager', 'cddu_ajax', [
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
    
    public function render_create_addendum_page(): void {
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        $instructors = get_users([
            'role' => 'cddu_instructor',
            'number' => -1
        ]);
        
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'numberposts' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_query' => [
                [
                    'key' => 'status',
                    'value' => ['draft', 'active', 'signed'],
                    'compare' => 'IN'
                ]
            ]
        ]);
        
        include CDDU_MNGR_PATH . 'templates/admin/create-addendum-form.php';
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
            'contract_content' => wp_kses_post($_POST['contract_content'] ?? ''),
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
            'contract_content' => wp_kses_post($_POST['contract_content'] ?? ''),
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
            
            // Use custom content if provided, otherwise use default template
            if (!empty($preview_data['contract_content'])) {
                $html = DocumentGenerator::processContractContent($preview_data['contract_content'], $template_data);
            } else {
                $template_path = CDDU_MNGR_PATH . 'templates/contracts/contract.html.php';
                $html = DocumentGenerator::renderTemplate($template_path, $template_data);
            }
            
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
    
    public function ajax_generate_addendum(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        // Collect all addendum form data
        $addendum_data = [
            'original_contract_id' => intval($_POST['original_contract_id'] ?? 0),
            'addendum_number' => intval($_POST['addendum_number'] ?? 1),
            'org_name' => sanitize_text_field($_POST['org_name'] ?? ''),
            'org_rcs_city' => sanitize_text_field($_POST['org_rcs_city'] ?? ''),
            'org_rcs_number' => sanitize_text_field($_POST['org_rcs_number'] ?? ''),
            'org_address' => sanitize_text_field($_POST['org_address'] ?? ''),
            'org_manager_title' => sanitize_text_field($_POST['org_manager_title'] ?? ''),
            'org_manager_name' => sanitize_text_field($_POST['org_manager_name'] ?? ''),
            'org_manager_role' => sanitize_text_field($_POST['org_manager_role'] ?? ''),
            'org_city' => sanitize_text_field($_POST['org_city'] ?? ''),
            'instructor_user_id' => intval($_POST['instructor_user_id'] ?? 0),
            'instructor_gender' => sanitize_text_field($_POST['instructor_gender'] ?? ''),
            'instructor_full_name' => sanitize_text_field($_POST['instructor_full_name'] ?? ''),
            'instructor_birth_date' => sanitize_text_field($_POST['instructor_birth_date'] ?? ''),
            'instructor_birth_place' => sanitize_text_field($_POST['instructor_birth_place'] ?? ''),
            'instructor_address' => sanitize_text_field($_POST['instructor_address'] ?? ''),
            'instructor_social_security' => sanitize_text_field($_POST['instructor_social_security'] ?? ''),
            'instructor_job_title' => sanitize_text_field($_POST['instructor_job_title'] ?? ''),
            'instructor_classification' => sanitize_text_field($_POST['instructor_classification'] ?? ''),
            'instructor_coefficient' => sanitize_text_field($_POST['instructor_coefficient'] ?? ''),
            'original_contract_date' => sanitize_text_field($_POST['original_contract_date'] ?? ''),
            'original_end_date' => sanitize_text_field($_POST['original_end_date'] ?? ''),
            'new_end_date' => sanitize_text_field($_POST['new_end_date'] ?? ''),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'effective_date' => sanitize_text_field($_POST['effective_date'] ?? ''),
            'signature_date' => sanitize_text_field($_POST['signature_date'] ?? ''),
            'november_total_hours' => floatval($_POST['november_total_hours'] ?? 0),
            'november_af_hours' => floatval($_POST['november_af_hours'] ?? 0),
            'november_pr_hours' => floatval($_POST['november_pr_hours'] ?? 0),
            'december_total_hours' => floatval($_POST['december_total_hours'] ?? 0),
            'december_af_hours' => floatval($_POST['december_af_hours'] ?? 0),
            'december_pr_hours' => floatval($_POST['december_pr_hours'] ?? 0),
            'january_total_hours' => floatval($_POST['january_total_hours'] ?? 0),
            'january_af_hours' => floatval($_POST['january_af_hours'] ?? 0),
            'january_pr_hours' => floatval($_POST['january_pr_hours'] ?? 0),
        ];
        
        // Process weekly schedule if provided
        if (!empty($_POST['weeks']) && is_array($_POST['weeks'])) {
            $weekly_schedule = [];
            foreach ($_POST['weeks'] as $week_data) {
                if (is_array($week_data)) {
                    $weekly_schedule[] = [
                        'start_date' => sanitize_text_field($week_data['start_date'] ?? ''),
                        'end_date' => sanitize_text_field($week_data['end_date'] ?? ''),
                        'af_hours' => floatval($week_data['af_hours'] ?? 0),
                        'pr_hours' => floatval($week_data['pr_hours'] ?? 0),
                    ];
                }
            }
            $addendum_data['weekly_schedule'] = $weekly_schedule;
        }
        
        // Validate required fields
        if (empty($addendum_data['instructor_user_id'])) {
            wp_send_json_error(['message' => __('Instructor is required', 'wp-cddu-manager')]);
        }
        
        try {
            // Get instructor user data for the addendum title
            $instructor_user = get_userdata($addendum_data['instructor_user_id']);
            $instructor_name = $instructor_user ? $instructor_user->display_name : __('Unknown Instructor', 'wp-cddu-manager');
            
            // Create addendum post
            $addendum_id = wp_insert_post([
                'post_type' => 'cddu_contract',
                'post_title' => sprintf(
                    __('Addendum #%d - %s - %s', 'wp-cddu-manager'),
                    $addendum_data['addendum_number'],
                    $instructor_name,
                    date('Y-m-d')
                ),
                'post_status' => 'draft',
                'meta_input' => [
                    'contract_type' => 'addendum',
                    'original_contract_id' => $addendum_data['original_contract_id'],
                    'addendum_number' => $addendum_data['addendum_number'],
                    'instructor_user_id' => $addendum_data['instructor_user_id'],
                    'addendum_data' => maybe_serialize($addendum_data),
                    'status' => 'draft',
                    'created_date' => current_time('mysql')
                ]
            ]);
            
            if (is_wp_error($addendum_id)) {
                wp_send_json_error(['message' => $addendum_id->get_error_message()]);
            }
            
            wp_send_json_success([
                'addendum_id' => $addendum_id,
                'edit_url' => admin_url('post.php?post=' . $addendum_id . '&action=edit'),
                'message' => __('Addendum created successfully', 'wp-cddu-manager')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function ajax_preview_addendum(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        // Collect addendum data for preview
        $addendum_data = [
            'number' => intval($_POST['addendum_number'] ?? 1),
            'original_contract_date' => sanitize_text_field($_POST['original_contract_date'] ?? ''),
            'original_end_date' => sanitize_text_field($_POST['original_end_date'] ?? ''),
            'new_end_date' => sanitize_text_field($_POST['new_end_date'] ?? ''),
            'effective_date' => sanitize_text_field($_POST['effective_date'] ?? ''),
            'signature_date' => sanitize_text_field($_POST['signature_date'] ?? ''),
            'monthly_breakdown' => [
                'Novembre 2023' => [
                    'hours' => number_format(floatval($_POST['november_total_hours'] ?? 0), 2, ',', ''),
                    'af_hours' => number_format(floatval($_POST['november_af_hours'] ?? 0), 2, ',', ''),
                    'pr_hours' => number_format(floatval($_POST['november_pr_hours'] ?? 0), 2, ',', ''),
                    'amount' => number_format((floatval($_POST['november_total_hours'] ?? 0) * floatval($_POST['hourly_rate'] ?? 13.17)), 2, ',', ''),
                ],
                'Décembre 2023' => [
                    'hours' => number_format(floatval($_POST['december_total_hours'] ?? 0), 2, ',', ''),
                    'af_hours' => number_format(floatval($_POST['december_af_hours'] ?? 0), 2, ',', ''),
                    'pr_hours' => number_format(floatval($_POST['december_pr_hours'] ?? 0), 2, ',', ''),
                    'amount' => number_format((floatval($_POST['december_total_hours'] ?? 0) * floatval($_POST['hourly_rate'] ?? 13.17)), 2, ',', ''),
                ],
                'Janvier 2024' => [
                    'hours' => number_format(floatval($_POST['january_total_hours'] ?? 0), 2, ',', ''),
                    'af_hours' => number_format(floatval($_POST['january_af_hours'] ?? 0), 2, ',', ''),
                    'pr_hours' => number_format(floatval($_POST['january_pr_hours'] ?? 0), 2, ',', ''),
                    'amount' => number_format((floatval($_POST['january_total_hours'] ?? 0) * floatval($_POST['hourly_rate'] ?? 13.17)), 2, ',', ''),
                ],
            ]
        ];
        
        // Process weekly schedule if provided
        if (!empty($_POST['weeks']) && is_array($_POST['weeks'])) {
            $weekly_schedule = [];
            foreach ($_POST['weeks'] as $week_data) {
                if (is_array($week_data) && !empty($week_data['start_date']) && !empty($week_data['end_date'])) {
                    $weekly_schedule[] = [
                        'start_date' => sanitize_text_field($week_data['start_date']),
                        'end_date' => sanitize_text_field($week_data['end_date']),
                        'af_hours' => number_format(floatval($week_data['af_hours'] ?? 0), 2, ',', ''),
                        'pr_hours' => number_format(floatval($week_data['pr_hours'] ?? 0), 2, ',', ''),
                    ];
                }
            }
            if (!empty($weekly_schedule)) {
                $addendum_data['weekly_schedule'] = $weekly_schedule;
            }
        }
        
        $organization = [
            'name' => sanitize_text_field($_POST['org_name'] ?? 'NEXT FORMA'),
            'rcs_city' => sanitize_text_field($_POST['org_rcs_city'] ?? 'Paris'),
            'rcs_number' => sanitize_text_field($_POST['org_rcs_number'] ?? '518 333 109'),
            'address' => sanitize_text_field($_POST['org_address'] ?? '77, Rue du Rocher – 75008 PARIS'),
            'manager_title' => sanitize_text_field($_POST['org_manager_title'] ?? 'Monsieur'),
            'manager_name' => sanitize_text_field($_POST['org_manager_name'] ?? 'Igal OINOUNOU'),
            'manager_role' => sanitize_text_field($_POST['org_manager_role'] ?? 'Gérant'),
            'city' => sanitize_text_field($_POST['org_city'] ?? 'Paris'),
        ];
        
        $instructor = [
            'gender' => sanitize_text_field($_POST['instructor_gender'] ?? 'Monsieur'),
            'full_name' => sanitize_text_field($_POST['instructor_full_name'] ?? '[...]'),
            'birth_date' => sanitize_text_field($_POST['instructor_birth_date'] ?? '[...]'),
            'birth_place' => sanitize_text_field($_POST['instructor_birth_place'] ?? '[...]'),
            'address' => sanitize_text_field($_POST['instructor_address'] ?? '[...]'),
            'social_security' => sanitize_text_field($_POST['instructor_social_security'] ?? '[...]'),
            'job_title' => sanitize_text_field($_POST['instructor_job_title'] ?? 'formateur en informatique'),
            'classification_level' => sanitize_text_field($_POST['instructor_classification'] ?? 'Palier 9'),
            'coefficient' => sanitize_text_field($_POST['instructor_coefficient'] ?? '200'),
        ];
        
        $mission = [
            'hourly_rate' => number_format(floatval($_POST['hourly_rate'] ?? 13.17), 2, ',', ''),
            'end_date' => sanitize_text_field($_POST['new_end_date'] ?? ''),
        ];
        
        $contract = [
            'original_date' => sanitize_text_field($_POST['original_contract_date'] ?? ''),
            'original_end_date' => sanitize_text_field($_POST['original_end_date'] ?? ''),
        ];
        
        try {
            $template_data = [
                'organization' => $organization,
                'instructor' => $instructor,
                'mission' => $mission,
                'contract' => $contract,
                'addendum_data' => $addendum_data,
                'calc' => [], // Could be expanded with calculations if needed
            ];
            
            $template_path = CDDU_MNGR_PATH . 'templates/addendums/addendum-next-forma.html.php';
            
            ob_start();
            extract($template_data);
            include $template_path;
            $html = ob_get_clean();
            
            wp_send_json_success(['html' => $html]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function ajax_get_instructor_data(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(['message' => __('Invalid user ID', 'wp-cddu-manager')]);
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(['message' => __('User not found', 'wp-cddu-manager')]);
        }
        
        $instructor_data = [
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'address' => get_user_meta($user_id, 'address', true),
            'birth_date' => get_user_meta($user_id, 'birth_date', true),
            'birth_place' => get_user_meta($user_id, 'birth_place', true),
            'social_security' => get_user_meta($user_id, 'social_security', true),
            'job_title' => get_user_meta($user_id, 'job_title', true),
            'classification_level' => get_user_meta($user_id, 'classification_level', true),
            'coefficient' => get_user_meta($user_id, 'coefficient', true),
        ];
        
        wp_send_json_success($instructor_data);
    }
    
    public function ajax_get_contract_data(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $contract_id = intval($_POST['contract_id'] ?? 0);
        if (!$contract_id) {
            wp_send_json_error(['message' => __('Invalid contract ID', 'wp-cddu-manager')]);
        }
        
        $contract = get_post($contract_id);
        if (!$contract || $contract->post_type !== 'cddu_contract') {
            wp_send_json_error(['message' => __('Contract not found', 'wp-cddu-manager')]);
        }
        
        $contract_data_meta = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data_meta);
        
        if (!is_array($contract_data)) {
            $contract_data = [];
        }
        
        $response_data = [
            'instructor_user_id' => get_post_meta($contract_id, 'instructor_user_id', true),
            'organization_id' => get_post_meta($contract_id, 'organization_id', true),
            'start_date' => $contract_data['start_date'] ?? '',
            'end_date' => $contract_data['end_date'] ?? '',
            'hourly_rate' => $contract_data['hourly_rate'] ?? '',
            'annual_hours' => $contract_data['annual_hours'] ?? '',
            'action' => $contract_data['action'] ?? '',
            'location' => $contract_data['location'] ?? '',
        ];
        
        wp_send_json_success($response_data);
    }
}
