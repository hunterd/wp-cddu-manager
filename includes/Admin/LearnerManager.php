<?php
namespace CDDU_Manager\Admin;

class LearnerManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_create_learner', [$this, 'ajax_create_learner']);
        add_action('wp_ajax_cddu_update_learner', [$this, 'ajax_update_learner']);
        add_action('wp_ajax_cddu_delete_learner', [$this, 'ajax_delete_learner']);
        add_action('wp_ajax_cddu_get_learner_data', [$this, 'ajax_get_learner_data']);
        add_action('wp_ajax_cddu_validate_learner_data', [$this, 'ajax_validate_learner_data']);
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
    }

    /**
     * Add learner help page to admin menu
     */
    public function add_admin_menu(): void {
        add_submenu_page(
            null, // No parent menu - this is a hidden page
            __('Learner Form Help', 'wp-cddu-manager'),
            __('Learner Form Help', 'wp-cddu-manager'),
            'edit_posts',
            'cddu-learner-help',
            [$this, 'render_help_page']
        );
    }

    /**
     * Render help page
     */
    public function render_help_page(): void {
        include CDDU_MNGR_PATH . 'templates/admin/learner-form-help.php';
    }

    /**
     * Register learner post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_learner', [
            'label' => __('Learners', 'wp-cddu-manager'),
            'labels' => [
                'name' => __('Learners', 'wp-cddu-manager'),
                'singular_name' => __('Learner', 'wp-cddu-manager'),
                'add_new' => __('Add New', 'wp-cddu-manager'),
                'add_new_item' => __('Add New Learner', 'wp-cddu-manager'),
                'edit_item' => __('Edit Learner', 'wp-cddu-manager'),
                'new_item' => __('New Learner', 'wp-cddu-manager'),
                'view_item' => __('View Learner', 'wp-cddu-manager'),
                'search_items' => __('Search Learners', 'wp-cddu-manager'),
                'not_found' => __('No learners found', 'wp-cddu-manager'),
                'not_found_in_trash' => __('No learners found in trash', 'wp-cddu-manager'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
            'menu_position' => 25,
        ]);
    }

    /**
     * Add learner metaboxes
     */
    public function add_metaboxes(): void {
        // Check if we're using the enhanced form (post-new.php or post.php for cddu_learner)
        global $pagenow;
        $is_enhanced_form = (
            ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'cddu_learner') ||
            ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'cddu_learner')
        );
        
        // Don't add metaboxes if using enhanced form
        if ($is_enhanced_form) {
            return;
        }
        
        add_meta_box('cddu_learner_details', __('Learner Details', 'wp-cddu-manager'), [$this, 'render_learner_metabox'], 'cddu_learner', 'normal', 'high');
        add_meta_box('cddu_learner_contact', __('Contact Information', 'wp-cddu-manager'), [$this, 'render_learner_contact_metabox'], 'cddu_learner', 'side', 'default');
    }

    /**
     * Render learner metabox
     */
    public function render_learner_metabox(\WP_Post $post): void {
        wp_nonce_field('cddu_learner_meta_nonce', 'cddu_learner_meta_nonce');

        $learner = get_post_meta($post->ID, 'learner', true);
        $learner = maybe_unserialize($learner) ?: [];

        include CDDU_MNGR_PATH . 'templates/partials/admin/learner-metabox.php';
    }

    /**
     * Render learner contact metabox
     */
    public function render_learner_contact_metabox(\WP_Post $post): void {
        $learner = get_post_meta($post->ID, 'learner', true);
        $learner = maybe_unserialize($learner) ?: [];

        include CDDU_MNGR_PATH . 'templates/partials/admin/learner-contact-metabox.php';
    }

    /**
     * Save learner meta
     */
    public function save_learner_meta(int $post_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
        if (!current_user_can('edit_post', $post_id)) { return; }
        if (!wp_verify_nonce($_POST['cddu_learner_meta_nonce'] ?? '', 'cddu_learner_meta_nonce')) { return; }

        $learner = $_POST['learner'] ?? [];
        if (!empty($learner)) {
            // Sanitize learner data
            $sanitized_learner = [
                'first_name' => sanitize_text_field($learner['first_name'] ?? ''),
                'last_name' => sanitize_text_field($learner['last_name'] ?? ''),
                'birth_date' => sanitize_text_field($learner['birth_date'] ?? ''),
                'social_security' => sanitize_text_field($learner['social_security'] ?? ''),
                'address' => sanitize_textarea_field($learner['address'] ?? ''),
                'level' => sanitize_text_field($learner['level'] ?? ''),
                'status' => sanitize_text_field($learner['status'] ?? 'active'),
                'email' => sanitize_email($learner['email'] ?? ''),
                'phone' => sanitize_text_field($learner['phone'] ?? ''),
                'emergency_contact' => sanitize_text_field($learner['emergency_contact'] ?? ''),
                'emergency_phone' => sanitize_text_field($learner['emergency_phone'] ?? ''),
            ];

            update_post_meta($post_id, 'learner', maybe_serialize($sanitized_learner));

            // Update post title with learner name if available
            if (!empty($sanitized_learner['first_name']) && !empty($sanitized_learner['last_name'])) {
                $learner_name = $sanitized_learner['first_name'] . ' ' . $sanitized_learner['last_name'];
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $learner_name
                ]);
            }
        }
    }

    /**
     * Remove pending status from learner post type
     */
    public function remove_pending_status_from_learners(): void {
        global $post;
        
        if (!$post || $post->post_type !== 'cddu_learner') {
            return;
        }
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Remove "En attente de relecture" (pending) option from status dropdown
            $('#post-status-select option[value="pending"]').remove();
            
            // Also remove it from the status display if it's currently pending
            if ($('#post-status-display').text().indexOf('En attente de relecture') !== -1 || 
                $('#post-status-display').text().indexOf('Pending Review') !== -1) {
                $('#post-status-display').text('Brouillon');
                $('#hidden-post-status').val('draft');
                $('#post_status').val('draft');
            }
        });
        </script>
        <?php
    }

    /**
     * Prevent pending status from being saved for learner post type
     */
    public function prevent_pending_status_for_learners(array $data, array $postarr): array {
        if (isset($data['post_type']) && $data['post_type'] === 'cddu_learner') {
            if ($data['post_status'] === 'pending') {
                $data['post_status'] = 'draft';
            }
        }
        
        return $data;
    }

    public function enqueue_scripts($hook): void {
        // Only load on learner post type pages (create/edit)
        if (!in_array($hook, ['post-new.php', 'post.php'])) {
            return;
        }
        
        // Only load on learner-related pages
        global $post_type;
        if ($post_type !== 'cddu_learner') {
            return;
        }
        
        wp_enqueue_script(
            'cddu-learner-manager',
            CDDU_MNGR_URL . 'assets/js/learner-manager.js',
            ['jquery'],
            CDDU_MNGR_VERSION,
            true
        );

        wp_enqueue_style(
            'cddu-learner-manager',
            CDDU_MNGR_URL . 'assets/css/learner-manager.css',
            [],
            CDDU_MNGR_VERSION
        );
        
        wp_localize_script('cddu-learner-manager', 'cddu_learner_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cddu_learner_nonce'),
            'strings' => [
                'confirm_delete' => __('Are you sure you want to delete this learner?', 'wp-cddu-manager'),
                'learner_created' => __('Learner created successfully', 'wp-cddu-manager'),
                'learner_updated' => __('Learner updated successfully', 'wp-cddu-manager'),
                'learner_deleted' => __('Learner deleted successfully', 'wp-cddu-manager'),
                'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                'loading' => __('Loading...', 'wp-cddu-manager'),
            ]
        ]);
    }

    public function ajax_create_learner(): void {
        check_ajax_referer('cddu_learner_nonce', 'nonce');
        
        // Collect all form data
        $learner_data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'birth_date' => sanitize_text_field($_POST['birth_date'] ?? ''),
            'social_security' => sanitize_text_field($_POST['social_security'] ?? ''),
            'address' => sanitize_textarea_field($_POST['address'] ?? ''),
            'level' => sanitize_text_field($_POST['level'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'active'),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'emergency_contact' => sanitize_text_field($_POST['emergency_contact'] ?? ''),
            'emergency_phone' => sanitize_text_field($_POST['emergency_phone'] ?? ''),
        ];
        
        // Validate required fields
        $validation_errors = $this->validate_learner_data($learner_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(['message' => implode(', ', $validation_errors)]);
        }
        
        try {
            $learner_name = $learner_data['first_name'] . ' ' . $learner_data['last_name'];
            
            // Create learner post
            $learner_id = wp_insert_post([
                'post_type' => 'cddu_learner',
                'post_title' => $learner_name,
                'post_status' => 'publish',
                'meta_input' => [
                    'learner' => maybe_serialize($learner_data),
                    'learner_status' => $learner_data['status'],
                    'learner_level' => $learner_data['level'],
                    'created_date' => current_time('mysql'),
                ]
            ]);
            
            if (is_wp_error($learner_id)) {
                wp_send_json_error(['message' => $learner_id->get_error_message()]);
            }
            
            wp_send_json_success([
                'learner_id' => $learner_id,
                'edit_url' => admin_url('post.php?post=' . $learner_id . '&action=edit'),
                'message' => __('Learner created successfully', 'wp-cddu-manager'),
                'learner_data' => $learner_data
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_update_learner(): void {
        check_ajax_referer('cddu_learner_nonce', 'nonce');
        
        $learner_id = intval($_POST['learner_id'] ?? 0);
        if (!$learner_id) {
            wp_send_json_error(['message' => __('Invalid learner ID', 'wp-cddu-manager')]);
        }
        
        // Check if learner exists
        $learner_post = get_post($learner_id);
        if (!$learner_post || $learner_post->post_type !== 'cddu_learner') {
            wp_send_json_error(['message' => __('Learner not found', 'wp-cddu-manager')]);
        }
        
        // Collect updated form data
        $learner_data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'birth_date' => sanitize_text_field($_POST['birth_date'] ?? ''),
            'social_security' => sanitize_text_field($_POST['social_security'] ?? ''),
            'address' => sanitize_textarea_field($_POST['address'] ?? ''),
            'level' => sanitize_text_field($_POST['level'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'active'),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'emergency_contact' => sanitize_text_field($_POST['emergency_contact'] ?? ''),
            'emergency_phone' => sanitize_text_field($_POST['emergency_phone'] ?? ''),
        ];
        
        // Validate required fields
        $validation_errors = $this->validate_learner_data($learner_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(['message' => implode(', ', $validation_errors)]);
        }
        
        try {
            $learner_name = $learner_data['first_name'] . ' ' . $learner_data['last_name'];
            
            // Update learner post
            $update_result = wp_update_post([
                'ID' => $learner_id,
                'post_title' => $learner_name,
            ]);
            
            if (is_wp_error($update_result)) {
                wp_send_json_error(['message' => $update_result->get_error_message()]);
            }
            
            // Update learner meta
            update_post_meta($learner_id, 'learner', maybe_serialize($learner_data));
            update_post_meta($learner_id, 'learner_status', $learner_data['status']);
            update_post_meta($learner_id, 'learner_level', $learner_data['level']);
            update_post_meta($learner_id, 'updated_date', current_time('mysql'));
            
            wp_send_json_success([
                'learner_id' => $learner_id,
                'message' => __('Learner updated successfully', 'wp-cddu-manager'),
                'learner_data' => $learner_data
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_delete_learner(): void {
        check_ajax_referer('cddu_learner_nonce', 'nonce');
        
        $learner_id = intval($_POST['learner_id'] ?? 0);
        if (!$learner_id) {
            wp_send_json_error(['message' => __('Invalid learner ID', 'wp-cddu-manager')]);
        }
        
        // Check if learner is assigned to any missions
        $assigned_missions = get_posts([
            'post_type' => 'cddu_mission',
            'meta_query' => [
                [
                    'key' => 'learner_ids',
                    'value' => serialize(strval($learner_id)),
                    'compare' => 'LIKE'
                ]
            ],
            'numberposts' => 1,
            'fields' => 'ids'
        ]);
        
        if (!empty($assigned_missions)) {
            wp_send_json_error(['message' => __('Cannot delete learner - they are assigned to active missions', 'wp-cddu-manager')]);
        }
        
        $result = wp_delete_post($learner_id, true);
        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete learner', 'wp-cddu-manager')]);
        }
        
        wp_send_json_success(['message' => __('Learner deleted successfully', 'wp-cddu-manager')]);
    }

    public function ajax_get_learner_data(): void {
        check_ajax_referer('cddu_learner_nonce', 'nonce');
        
        $learner_id = intval($_POST['learner_id'] ?? 0);
        if (!$learner_id) {
            wp_send_json_error(['message' => __('Invalid learner ID', 'wp-cddu-manager')]);
        }
        
        $learner_post = get_post($learner_id);
        if (!$learner_post || $learner_post->post_type !== 'cddu_learner') {
            wp_send_json_error(['message' => __('Learner not found', 'wp-cddu-manager')]);
        }
        
        $learner_meta = get_post_meta($learner_id, 'learner', true);
        $learner_data = maybe_unserialize($learner_meta) ?: [];
        
        wp_send_json_success([
            'learner_id' => $learner_id,
            'learner_data' => $learner_data
        ]);
    }

    public function ajax_validate_learner_data(): void {
        check_ajax_referer('cddu_learner_nonce', 'nonce');
        
        $learner_data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
        ];
        
        $validation_errors = $this->validate_learner_data($learner_data);
        
        if (!empty($validation_errors)) {
            wp_send_json_error(['errors' => $validation_errors]);
        }
        
        wp_send_json_success(['message' => __('Learner data is valid', 'wp-cddu-manager')]);
    }

    private function validate_learner_data(array $learner_data): array {
        $errors = [];
        
        if (empty($learner_data['first_name'])) {
            $errors[] = __('First name is required', 'wp-cddu-manager');
        }
        
        if (empty($learner_data['last_name'])) {
            $errors[] = __('Last name is required', 'wp-cddu-manager');
        }
        
        if (!empty($learner_data['email']) && !is_email($learner_data['email'])) {
            $errors[] = __('Invalid email address', 'wp-cddu-manager');
        }
        
        return $errors;
    }
}
