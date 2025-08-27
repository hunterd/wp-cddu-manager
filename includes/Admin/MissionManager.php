<?php
namespace CDDU_Manager\Admin;

use CDDU_Manager\Calculations;

class MissionManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_create_mission', [$this, 'ajax_create_mission']);
        add_action('wp_ajax_cddu_update_mission', [$this, 'ajax_update_mission']);
        add_action('wp_ajax_cddu_delete_mission', [$this, 'ajax_delete_mission']);
        add_action('wp_ajax_cddu_get_mission_data', [$this, 'ajax_get_mission_data']);
        add_action('wp_ajax_cddu_get_missions_for_organization', [$this, 'ajax_get_missions_for_organization']);
        add_action('wp_ajax_cddu_validate_mission_data', [$this, 'ajax_validate_mission_data']);
    }

    /**
     * Register mission post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_mission', [
            'label' => __('Missions', 'wp-cddu-manager'),
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => ['title'],
            'capability_type' => 'cddu_mission',
            'capabilities' => [
                'create_posts' => 'cddu_create_missions_via_standard_ui', // Capability that nobody has
                'edit_post' => 'edit_post',
                'read_post' => 'read_post',
                'delete_post' => 'delete_post',
                'edit_posts' => 'edit_posts',
                'edit_others_posts' => 'edit_others_posts',
                'read_private_posts' => 'read_private_posts',
                'delete_posts' => 'delete_posts',
                'delete_private_posts' => 'delete_private_posts',
                'delete_published_posts' => 'delete_published_posts',
                'delete_others_posts' => 'delete_others_posts',
                'edit_private_posts' => 'edit_private_posts',
                'edit_published_posts' => 'edit_published_posts',
            ],
            'map_meta_cap' => true,
        ]);
    }

    /**
     * Add mission metaboxes
     */
    public function add_metaboxes(): void {
        add_meta_box('cddu_mission_details', __('Mission Details', 'wp-cddu-manager'), [$this, 'render_mission_metabox'], 'cddu_mission', 'normal', 'high');
    }

    /**
     * Render mission metabox
     */
    public function render_mission_metabox(\WP_Post $post): void {
        $meta = get_post_meta($post->ID);
        $mission = $meta['mission'] ? maybe_unserialize($meta['mission'][0]) : [];
        
        include CDDU_MNGR_PATH . 'templates/partials/admin/mission-metabox.php';
    }

    /**
     * Save mission meta
     */
    public function save_mission_meta(int $post_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
        if (!current_user_can('edit_post', $post_id)) { return; }

        $mission = $_POST['mission'] ?? null;
        if ($mission !== null) {
            update_post_meta($post_id, 'mission', maybe_serialize($mission));
        }
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_mission',
            __('Create Mission', 'wp-cddu-manager'),
            __('Create Mission', 'wp-cddu-manager'),
            'cddu_manage',
            'create-mission',
            [$this, 'render_create_mission_page']
        );

        add_submenu_page(
            'edit.php?post_type=cddu_mission',
            __('Manage Missions', 'wp-cddu-manager'),
            __('Manage Missions', 'wp-cddu-manager'),
            'cddu_manage',
            'manage-missions',
            [$this, 'render_manage_missions_page']
        );
    }

    public function enqueue_scripts($hook): void {
        // Enqueue for mission creation and management pages
        if (in_array($hook, ['cddu_mission_page_create-mission', 'cddu_mission_page_manage-missions'])) {
            wp_enqueue_script(
                'cddu-mission-manager',
                CDDU_MNGR_URL . 'assets/js/mission-manager.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );

            wp_enqueue_style(
                'cddu-mission-manager',
                CDDU_MNGR_URL . 'assets/css/mission-manager.css',
                [],
                CDDU_MNGR_VERSION
            );
        }
        
        // Enqueue for mission edit pages (metaboxes)
        global $pagenow, $post_type;
        if (($pagenow === 'post.php' || $pagenow === 'post-new.php') && $post_type === 'cddu_mission') {
            wp_enqueue_style(
                'cddu-mission-metabox',
                CDDU_MNGR_URL . 'assets/css/mission-metabox.css',
                [],
                CDDU_MNGR_VERSION
            );

            wp_enqueue_script(
                'cddu-mission-metabox',
                CDDU_MNGR_URL . 'assets/js/mission-metabox.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
        }
        
        // Only add localization for mission manager pages
        if (!in_array($hook, ['cddu_mission_page_create-mission', 'cddu_mission_page_manage-missions'])) {
            return;
        }
        
        wp_localize_script('cddu-mission-manager', 'cddu_mission_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cddu_mission_nonce'),
            'strings' => [
                'confirm_delete' => __('Are you sure you want to delete this mission?', 'wp-cddu-manager'),
                'mission_created' => __('Mission created successfully', 'wp-cddu-manager'),
                'mission_updated' => __('Mission updated successfully', 'wp-cddu-manager'),
                'mission_deleted' => __('Mission deleted successfully', 'wp-cddu-manager'),
                'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                'loading' => __('Loading...', 'wp-cddu-manager'),
            ]
        ]);
    }

    public function render_create_mission_page(): void {
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        $learners = get_posts([
            'post_type' => 'cddu_learner',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        include CDDU_MNGR_PATH . 'templates/admin/create-mission-form.php';
    }

    public function render_manage_missions_page(): void {
        $missions = get_posts([
            'post_type' => 'cddu_mission',
            'numberposts' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'mission_status',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'mission_status',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);

        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        include CDDU_MNGR_PATH . 'templates/admin/manage-missions.php';
    }

    public function ajax_create_mission(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        // Collect all form data
        $mission_data = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => wp_kses_post($_POST['description'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
            'total_hours' => floatval($_POST['total_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'required_skills' => array_map('sanitize_text_field', $_POST['required_skills'] ?? []),
            'mission_type' => sanitize_text_field($_POST['mission_type'] ?? 'standard'),
            'priority' => sanitize_text_field($_POST['priority'] ?? 'medium'),
            'status' => sanitize_text_field($_POST['status'] ?? 'draft'),
            // New fields for complete mission entry
            'learner_ids' => array_map('intval', $_POST['learner_ids'] ?? []),
            'training_action' => sanitize_text_field($_POST['training_action'] ?? ''),
            'training_modalities' => array_map('sanitize_text_field', $_POST['training_modalities'] ?? []),
        ];
        
        // Validate required fields
        $validation_errors = $this->validate_mission_data($mission_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(['message' => implode(', ', $validation_errors)]);
        }
        
        try {
            // Create mission post
            $mission_id = wp_insert_post([
                'post_type' => 'cddu_mission',
                'post_title' => $mission_data['title'],
                'post_content' => $mission_data['description'],
                'post_status' => 'publish',
                'meta_input' => [
                    'mission' => maybe_serialize($mission_data),
                    'organization_id' => $mission_data['organization_id'],
                    'mission_status' => $mission_data['status'],
                    'start_date' => $mission_data['start_date'],
                    'end_date' => $mission_data['end_date'],
                    'total_hours' => $mission_data['total_hours'],
                    'hourly_rate' => $mission_data['hourly_rate'],
                    'location' => $mission_data['location'],
                    'mission_type' => $mission_data['mission_type'],
                    'priority' => $mission_data['priority'],
                    'required_skills' => maybe_serialize($mission_data['required_skills']),
                    // New metadata for complete mission entry
                    'learner_ids' => maybe_serialize($mission_data['learner_ids']),
                    'training_action' => $mission_data['training_action'],
                    'training_modalities' => maybe_serialize($mission_data['training_modalities']),
                    'created_date' => current_time('mysql'),
                ]
            ]);
            
            if (is_wp_error($mission_id)) {
                wp_send_json_error(['message' => $mission_id->get_error_message()]);
            }
            
            // Calculate mission statistics
            $mission_stats = $this->calculate_mission_stats($mission_data);
            update_post_meta($mission_id, 'mission_stats', maybe_serialize($mission_stats));
            
            wp_send_json_success([
                'mission_id' => $mission_id,
                'edit_url' => admin_url('post.php?post=' . $mission_id . '&action=edit'),
                'message' => __('Mission created successfully', 'wp-cddu-manager'),
                'mission_data' => $mission_data,
                'mission_stats' => $mission_stats
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_update_mission(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        $mission_id = intval($_POST['mission_id'] ?? 0);
        if (!$mission_id) {
            wp_send_json_error(['message' => __('Invalid mission ID', 'wp-cddu-manager')]);
        }
        
        // Check if mission exists
        $mission_post = get_post($mission_id);
        if (!$mission_post || $mission_post->post_type !== 'cddu_mission') {
            wp_send_json_error(['message' => __('Mission not found', 'wp-cddu-manager')]);
        }
        
        // Collect updated form data
        $mission_data = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => wp_kses_post($_POST['description'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
            'total_hours' => floatval($_POST['total_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'required_skills' => array_map('sanitize_text_field', $_POST['required_skills'] ?? []),
            'mission_type' => sanitize_text_field($_POST['mission_type'] ?? 'standard'),
            'priority' => sanitize_text_field($_POST['priority'] ?? 'medium'),
            'status' => sanitize_text_field($_POST['status'] ?? 'draft'),
            // New fields for complete mission entry
            'learner_ids' => array_map('intval', $_POST['learner_ids'] ?? []),
            'training_action' => sanitize_text_field($_POST['training_action'] ?? ''),
            'training_modalities' => array_map('sanitize_text_field', $_POST['training_modalities'] ?? []),
        ];
        
        // Validate required fields
        $validation_errors = $this->validate_mission_data($mission_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(['message' => implode(', ', $validation_errors)]);
        }
        
        try {
            // Update mission post
            $update_result = wp_update_post([
                'ID' => $mission_id,
                'post_title' => $mission_data['title'],
                'post_content' => $mission_data['description'],
            ]);
            
            if (is_wp_error($update_result)) {
                wp_send_json_error(['message' => $update_result->get_error_message()]);
            }
            
            // Update mission meta
            update_post_meta($mission_id, 'mission', maybe_serialize($mission_data));
            update_post_meta($mission_id, 'organization_id', $mission_data['organization_id']);
            update_post_meta($mission_id, 'mission_status', $mission_data['status']);
            update_post_meta($mission_id, 'start_date', $mission_data['start_date']);
            update_post_meta($mission_id, 'end_date', $mission_data['end_date']);
            update_post_meta($mission_id, 'total_hours', $mission_data['total_hours']);
            update_post_meta($mission_id, 'hourly_rate', $mission_data['hourly_rate']);
            update_post_meta($mission_id, 'location', $mission_data['location']);
            update_post_meta($mission_id, 'mission_type', $mission_data['mission_type']);
            update_post_meta($mission_id, 'priority', $mission_data['priority']);
            update_post_meta($mission_id, 'required_skills', maybe_serialize($mission_data['required_skills']));
            // New metadata for complete mission entry
            update_post_meta($mission_id, 'learner_ids', maybe_serialize($mission_data['learner_ids']));
            update_post_meta($mission_id, 'training_action', $mission_data['training_action']);
            update_post_meta($mission_id, 'training_modalities', maybe_serialize($mission_data['training_modalities']));
            update_post_meta($mission_id, 'updated_date', current_time('mysql'));
            
            // Recalculate mission statistics
            $mission_stats = $this->calculate_mission_stats($mission_data);
            update_post_meta($mission_id, 'mission_stats', maybe_serialize($mission_stats));
            
            wp_send_json_success([
                'mission_id' => $mission_id,
                'message' => __('Mission updated successfully', 'wp-cddu-manager'),
                'mission_data' => $mission_data,
                'mission_stats' => $mission_stats
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_delete_mission(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        $mission_id = intval($_POST['mission_id'] ?? 0);
        if (!$mission_id) {
            wp_send_json_error(['message' => __('Invalid mission ID', 'wp-cddu-manager')]);
        }
        
        // Check if mission has active contracts
        $active_contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'mission_id',
                    'value' => $mission_id,
                    'compare' => '='
                ],
                [
                    'key' => 'status',
                    'value' => ['active', 'signed', 'pending'],
                    'compare' => 'IN'
                ]
            ],
            'numberposts' => 1,
            'fields' => 'ids'
        ]);
        
        if (!empty($active_contracts)) {
            wp_send_json_error([
                'message' => __('Cannot delete mission with active contracts. Please complete or cancel contracts first.', 'wp-cddu-manager')
            ]);
        }
        
        try {
            $delete_result = wp_delete_post($mission_id, true);
            
            if (!$delete_result) {
                wp_send_json_error(['message' => __('Failed to delete mission', 'wp-cddu-manager')]);
            }
            
            wp_send_json_success([
                'message' => __('Mission deleted successfully', 'wp-cddu-manager')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_get_mission_data(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        $mission_id = intval($_POST['mission_id'] ?? 0);
        if (!$mission_id) {
            wp_send_json_error(['message' => __('Invalid mission ID', 'wp-cddu-manager')]);
        }
        
        $mission_post = get_post($mission_id);
        if (!$mission_post || $mission_post->post_type !== 'cddu_mission') {
            wp_send_json_error(['message' => __('Mission not found', 'wp-cddu-manager')]);
        }
        
        $mission_meta = get_post_meta($mission_id, 'mission', true);
        $mission_data = maybe_unserialize($mission_meta);
        
        if (!is_array($mission_data)) {
            $mission_data = [];
        }
        
        // Get additional meta data
        $mission_data['mission_id'] = $mission_id;
        $mission_data['title'] = $mission_post->post_title;
        $mission_data['description'] = $mission_post->post_content;
        $mission_data['organization_id'] = get_post_meta($mission_id, 'organization_id', true);
        $mission_data['mission_status'] = get_post_meta($mission_id, 'mission_status', true);
        $mission_data['required_skills'] = maybe_unserialize(get_post_meta($mission_id, 'required_skills', true)) ?: [];
        
        // Get mission statistics
        $mission_stats = maybe_unserialize(get_post_meta($mission_id, 'mission_stats', true)) ?: [];
        
        wp_send_json_success([
            'mission_data' => $mission_data,
            'mission_stats' => $mission_stats
        ]);
    }

    public function ajax_get_missions_for_organization(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        $organization_id = intval($_POST['organization_id'] ?? 0);
        if (!$organization_id) {
            wp_send_json_error(['message' => __('Invalid organization ID', 'wp-cddu-manager')]);
        }
        
        $missions = get_posts([
            'post_type' => 'cddu_mission',
            'numberposts' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'organization_id',
                    'value' => $organization_id,
                    'compare' => '='
                ]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        $missions_data = [];
        foreach ($missions as $mission) {
            $mission_meta = get_post_meta($mission->ID, 'mission', true);
            $mission_data = maybe_unserialize($mission_meta) ?: [];
            
            $missions_data[] = [
                'id' => $mission->ID,
                'title' => $mission->post_title,
                'status' => get_post_meta($mission->ID, 'mission_status', true),
                'start_date' => $mission_data['start_date'] ?? '',
                'end_date' => $mission_data['end_date'] ?? '',
                'total_hours' => $mission_data['total_hours'] ?? 0,
                'hourly_rate' => $mission_data['hourly_rate'] ?? 0,
                'location' => $mission_data['location'] ?? '',
                'priority' => $mission_data['priority'] ?? 'medium',
            ];
        }
        
        wp_send_json_success(['missions' => $missions_data]);
    }

    public function ajax_validate_mission_data(): void {
        check_ajax_referer('cddu_mission_nonce', 'nonce');
        
        $mission_data = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
            'total_hours' => floatval($_POST['total_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
        ];
        
        $validation_errors = $this->validate_mission_data($mission_data);
        
        if (!empty($validation_errors)) {
            wp_send_json_error(['errors' => $validation_errors]);
        }
        
        wp_send_json_success(['message' => __('Mission data is valid', 'wp-cddu-manager')]);
    }

    private function validate_mission_data(array $mission_data): array {
        $errors = [];
        
        if (empty($mission_data['organization_id'])) {
            $errors[] = __('Organization is required', 'wp-cddu-manager');
        }
        
        if (empty($mission_data['title'])) {
            $errors[] = __('Mission title is required', 'wp-cddu-manager');
        }
        
        if (empty($mission_data['start_date'])) {
            $errors[] = __('Start date is required', 'wp-cddu-manager');
        }
        
        if (empty($mission_data['end_date'])) {
            $errors[] = __('End date is required', 'wp-cddu-manager');
        }
        
        if (!empty($mission_data['start_date']) && !empty($mission_data['end_date'])) {
            $start_timestamp = strtotime($mission_data['start_date']);
            $end_timestamp = strtotime($mission_data['end_date']);
            
            if ($start_timestamp >= $end_timestamp) {
                $errors[] = __('End date must be after start date', 'wp-cddu-manager');
            }
        }
        
        if ($mission_data['total_hours'] <= 0) {
            $errors[] = __('Total hours must be greater than 0', 'wp-cddu-manager');
        }
        
        if ($mission_data['hourly_rate'] <= 0) {
            $errors[] = __('Hourly rate must be greater than 0', 'wp-cddu-manager');
        }

        // Validation for new required fields
        if (empty($mission_data['training_action'])) {
            $errors[] = __('Training action is required', 'wp-cddu-manager');
        }

        if (empty($mission_data['learner_ids']) || !is_array($mission_data['learner_ids'])) {
            $errors[] = __('At least one learner must be assigned', 'wp-cddu-manager');
        }

        if (empty($mission_data['training_modalities']) || !is_array($mission_data['training_modalities'])) {
            $errors[] = __('Training modalities are required', 'wp-cddu-manager');
        }
        
        return $errors;
    }

    private function calculate_mission_stats(array $mission_data): array {
        $start_date = strtotime($mission_data['start_date']);
        $end_date = strtotime($mission_data['end_date']);
        $duration_days = ($end_date - $start_date) / (24 * 60 * 60);
        
        $total_budget = $mission_data['total_hours'] * $mission_data['hourly_rate'];
        $hours_per_day = $duration_days > 0 ? $mission_data['total_hours'] / $duration_days : 0;
        
        return [
            'duration_days' => $duration_days,
            'total_budget' => $total_budget,
            'hours_per_day' => round($hours_per_day, 2),
            'weekly_hours' => round($hours_per_day * 7, 2),
            'calculated_at' => current_time('mysql')
        ];
    }
}
