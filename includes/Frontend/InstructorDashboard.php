<?php
namespace CDDU_Manager\Frontend;

use CDDU_Manager\Calculations;

class InstructorDashboard {
    public function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_instructor_pages']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX handlers for instructor actions
        add_action('wp_ajax_cddu_submit_timesheet', [$this, 'ajax_submit_timesheet']);
        add_action('wp_ajax_cddu_get_instructor_contracts', [$this, 'ajax_get_instructor_contracts']);
    }

    public function add_rewrite_rules(): void {
        add_rewrite_rule('^instructor-dashboard/?$', 'index.php?cddu_instructor_dashboard=1', 'top');
        add_rewrite_rule('^instructor-dashboard/contract/([0-9]+)/?$', 'index.php?cddu_instructor_dashboard=1&contract_id=$matches[1]', 'top');
        add_rewrite_rule('^instructor-dashboard/timesheet/?$', 'index.php?cddu_instructor_dashboard=1&action=timesheet', 'top');
        
        add_rewrite_tag('%cddu_instructor_dashboard%', '([^&]+)');
        add_rewrite_tag('%contract_id%', '([0-9]+)');
        add_rewrite_tag('%action%', '([^&]+)');
    }

    public function handle_instructor_pages(): void {
        $dashboard = get_query_var('cddu_instructor_dashboard');
        if (!$dashboard) {
            return;
        }
        
        // Check if user is logged in and has instructor role
        if (!is_user_logged_in() || !current_user_can('cddu_instructor')) {
            wp_redirect(wp_login_url(home_url('/instructor-dashboard/')));
            exit;
        }
        
        $contract_id = get_query_var('contract_id');
        $action = get_query_var('action');
        
        if ($contract_id) {
            $this->render_contract_view($contract_id);
        } elseif ($action === 'timesheet') {
            $this->render_timesheet_form();
        } else {
            $this->render_dashboard();
        }
        
        exit;
    }

    public function enqueue_scripts(): void {
        if (get_query_var('cddu_instructor_dashboard')) {
            wp_enqueue_script(
                'cddu-instructor-dashboard',
                CDDU_MNGR_URL . 'assets/js/instructor-dashboard.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
            
            wp_localize_script('cddu-instructor-dashboard', 'cddu_instructor_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cddu_instructor_nonce'),
            ]);
            
            wp_enqueue_style(
                'cddu-instructor-dashboard',
                CDDU_MNGR_URL . 'assets/css/instructor-dashboard.css',
                [],
                CDDU_MNGR_VERSION
            );
        }
    }

    private function render_dashboard(): void {
        $current_user = wp_get_current_user();
        
        // Get instructor post linked to current user
                
        $current_user = wp_get_current_user();
        
        // Get contracts for this instructor user directly
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'instructor_user_id',
                    'value' => $current_user->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1,
            'post_status' => ['draft', 'publish']
        ]);
        
        include CDDU_MNGR_PATH . 'templates/frontend/instructor-dashboard.php';
    }

    private function render_contract_view(int $contract_id): void {
        $current_user = wp_get_current_user();
        
        // Verify this contract belongs to current instructor
        $contract_instructor_user_id = get_post_meta($contract_id, 'instructor_user_id', true);
        
        if ($contract_instructor_user_id != $current_user->ID) {
            wp_die(__('You are not authorized to view this contract.', 'wp-cddu-manager'));
        }
        
        $contract = get_post($contract_id);
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $calculations = get_post_meta($contract_id, 'calculations', true);
        
        if ($contract_data) {
            $contract_data = maybe_unserialize($contract_data);
        }
        if ($calculations) {
            $calculations = maybe_unserialize($calculations);
        }
        
        include CDDU_MNGR_PATH . 'templates/frontend/contract-view.php';
    }

    private function render_timesheet_form(): void {
        $current_user = wp_get_current_user();
        
        // Use current instructor user directly
        $instructor_user_id = $current_user->ID;
        
        // Get active contracts for this instructor
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'instructor_user_id',
                    'value' => $instructor_user_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        // Get existing timesheets
        $timesheets = get_posts([
            'post_type' => 'cddu_timesheet',
            'meta_query' => [
                [
                    'key' => 'instructor_user_id',
                    'value' => $instructor_user_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        include CDDU_MNGR_PATH . 'templates/frontend/timesheet-form.php';
    }

    public function ajax_submit_timesheet(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');
        
        if (!current_user_can('cddu_instructor')) {
            wp_send_json_error(['message' => __('Access denied', 'wp-cddu-manager')]);
        }
        
        $timesheet_data = [
            'contract_id' => intval($_POST['contract_id'] ?? 0),
            'month' => sanitize_text_field($_POST['month'] ?? ''),
            'year' => intval($_POST['year'] ?? date('Y')),
            'hours_worked' => floatval($_POST['hours_worked'] ?? 0),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
        ];
        
        // Get current instructor user ID
        $current_user = wp_get_current_user();
        $instructor_user_id = $current_user->ID;
        
        // Verify contract belongs to instructor
        $contract_instructor_id = get_post_meta($timesheet_data['contract_id'], 'instructor_user_id', true);
        if ($contract_instructor_id != $instructor_user_id) {
            wp_send_json_error(['message' => __('Invalid contract', 'wp-cddu-manager')]);
        }
        
        // Check if timesheet already exists for this month/year/contract
        $existing = get_posts([
            'post_type' => 'cddu_timesheet',
            'meta_query' => [
                [
                    'key' => 'instructor_user_id',
                    'value' => $instructor_user_id,
                    'compare' => '='
                ],
                [
                    'key' => 'contract_id',
                    'value' => $timesheet_data['contract_id'],
                    'compare' => '='
                ],
                [
                    'key' => 'month',
                    'value' => $timesheet_data['month'],
                    'compare' => '='
                ],
                [
                    'key' => 'year',
                    'value' => $timesheet_data['year'],
                    'compare' => '='
                ]
            ],
            'numberposts' => 1
        ]);
        
        if (!empty($existing)) {
            wp_send_json_error(['message' => __('Timesheet for this month already exists', 'wp-cddu-manager')]);
        }
        
        try {
            // Create timesheet post
            $timesheet_id = wp_insert_post([
                'post_type' => 'cddu_timesheet',
                'post_title' => sprintf(
                    __('Timesheet %s %d - %s', 'wp-cddu-manager'),
                    $timesheet_data['month'],
                    $timesheet_data['year'],
                    get_the_title($timesheet_data['contract_id'])
                ),
                'post_status' => 'publish',
                'meta_input' => [
                    'instructor_user_id' => $instructor_user_id,
                    'contract_id' => $timesheet_data['contract_id'],
                    'month' => $timesheet_data['month'],
                    'year' => $timesheet_data['year'],
                    'hours_worked' => $timesheet_data['hours_worked'],
                    'description' => $timesheet_data['description'],
                    'submitted_date' => current_time('mysql'),
                    'status' => 'submitted'
                ]
            ]);
            
            if (is_wp_error($timesheet_id)) {
                wp_send_json_error(['message' => $timesheet_id->get_error_message()]);
            }
            
            // TODO: Check if hours exceed planned hours and trigger addendum generation
            
            wp_send_json_success([
                'timesheet_id' => $timesheet_id,
                'message' => __('Timesheet submitted successfully', 'wp-cddu-manager')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_get_instructor_contracts(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');
        
        if (!current_user_can('cddu_instructor')) {
            wp_send_json_error(['message' => __('Access denied', 'wp-cddu-manager')]);
        }
        
        $current_user = wp_get_current_user();
        
        // Get contracts for this instructor user directly
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'instructor_user_id',
                    'value' => $current_user->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        $contract_data = [];
        foreach ($contracts as $contract) {
            $contract_info = get_post_meta($contract->ID, 'contract_data', true);
            if ($contract_info) {
                $contract_info = maybe_unserialize($contract_info);
            }
            
            $contract_data[] = [
                'id' => $contract->ID,
                'title' => $contract->post_title,
                'action' => $contract_info['action'] ?? '',
                'start_date' => $contract_info['start_date'] ?? '',
                'end_date' => $contract_info['end_date'] ?? '',
                'status' => get_post_meta($contract->ID, 'status', true) ?: 'draft'
            ];
        }
        
        wp_send_json_success($contract_data);
    }
}
