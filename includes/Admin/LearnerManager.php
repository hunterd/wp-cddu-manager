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
        
        // Hook to replace learner edit interface
        add_action('add_meta_boxes', [$this, 'add_learner_edit_metaboxes']);
        add_action('add_meta_boxes', [$this, 'remove_default_metaboxes'], 999);
        add_action('save_post_cddu_learner', [$this, 'save_learner_meta'], 10, 2);
    }

    /**
     * Add learner help page to admin menu
     */
    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_learner',
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
     * Add learner edit metaboxes
     */
    public function add_learner_edit_metaboxes(): void {
        // Personal Information metabox
        add_meta_box(
            'cddu-learner-personal',
            __('Personal Information', 'wp-cddu-manager'),
            [$this, 'render_personal_information_metabox'],
            'cddu_learner',
            'normal',
            'high'
        );
        
        // Address Information metabox
        add_meta_box(
            'cddu-learner-address',
            __('Address Information', 'wp-cddu-manager'),
            [$this, 'render_address_information_metabox'],
            'cddu_learner',
            'normal',
            'high'
        );
        
        // Contact Information metabox
        add_meta_box(
            'cddu-learner-contact',
            __('Contact Information', 'wp-cddu-manager'),
            [$this, 'render_contact_information_metabox'],
            'cddu_learner',
            'normal',
            'high'
        );
        
        // Academic Information metabox
        add_meta_box(
            'cddu-learner-academic',
            __('Academic Information', 'wp-cddu-manager'),
            [$this, 'render_academic_information_metabox'],
            'cddu_learner',
            'normal',
            'high'
        );
        
        // Additional Notes metabox
        add_meta_box(
            'cddu-learner-notes',
            __('Additional Notes', 'wp-cddu-manager'),
            [$this, 'render_additional_notes_metabox'],
            'cddu_learner',
            'normal',
            'high'
        );
    }
    
    /**
     * Remove default WordPress metaboxes for learners
     */
    public function remove_default_metaboxes(): void {
        // Remove custom fields metabox
        remove_meta_box('postcustom', 'cddu_learner', 'normal');
        remove_meta_box('postcustom', 'cddu_learner', 'advanced');
        
        // Remove slug metabox
        remove_meta_box('slugdiv', 'cddu_learner', 'normal');
        remove_meta_box('slugdiv', 'cddu_learner', 'advanced');
        
        // Remove excerpt metabox if it exists
        remove_meta_box('postexcerpt', 'cddu_learner', 'normal');
        remove_meta_box('postexcerpt', 'cddu_learner', 'advanced');
        
        // Remove trackbacks metabox
        remove_meta_box('trackbacksdiv', 'cddu_learner', 'normal');
        remove_meta_box('trackbacksdiv', 'cddu_learner', 'advanced');
        
        // Remove comments metabox
        remove_meta_box('commentstatusdiv', 'cddu_learner', 'normal');
        remove_meta_box('commentstatusdiv', 'cddu_learner', 'advanced');
        
        // Remove author metabox
        remove_meta_box('authordiv', 'cddu_learner', 'normal');
        remove_meta_box('authordiv', 'cddu_learner', 'advanced');
    }

    /**
     * Save learner meta data
     */
    public function save_learner_meta($post_id, $post): void {
        // Verify nonce
        if (!isset($_POST['cddu_learner_nonce']) || !wp_verify_nonce($_POST['cddu_learner_nonce'], 'cddu_learner_nonce')) {
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Avoid infinite loops
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Save learner meta data from metaboxes
        $meta_fields = [
            // Personal Information
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'birth_date' => sanitize_text_field($_POST['birth_date'] ?? ''),
            'birth_place' => sanitize_text_field($_POST['birth_place'] ?? ''),
            'nationality' => sanitize_text_field($_POST['nationality'] ?? ''),
            'social_security' => sanitize_text_field($_POST['social_security'] ?? ''),
            'gender' => sanitize_text_field($_POST['gender'] ?? ''),
            
            // Address Information
            'address' => sanitize_textarea_field($_POST['address'] ?? ''),
            'city' => sanitize_text_field($_POST['city'] ?? ''),
            'postal_code' => sanitize_text_field($_POST['postal_code'] ?? ''),
            'country' => sanitize_text_field($_POST['country'] ?? ''),
            
            // Contact Information
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'mobile_phone' => sanitize_text_field($_POST['mobile_phone'] ?? ''),
            'emergency_contact' => sanitize_text_field($_POST['emergency_contact'] ?? ''),
            'emergency_phone' => sanitize_text_field($_POST['emergency_phone'] ?? ''),
            'emergency_relationship' => sanitize_text_field($_POST['emergency_relationship'] ?? ''),
            
            // Academic Information
            'level' => sanitize_text_field($_POST['level'] ?? ''),
            'diploma' => sanitize_text_field($_POST['diploma'] ?? ''),
            'specialization' => sanitize_text_field($_POST['specialization'] ?? ''),
            'previous_experience' => sanitize_textarea_field($_POST['previous_experience'] ?? ''),
            'skills' => array_map('sanitize_text_field', $_POST['skills'] ?? []),
            'status' => sanitize_text_field($_POST['status'] ?? 'active'),
            
            // Additional Notes
            'notes' => sanitize_textarea_field($_POST['notes'] ?? ''),
            'medical_notes' => sanitize_textarea_field($_POST['medical_notes'] ?? ''),
            'special_requirements' => sanitize_textarea_field($_POST['special_requirements'] ?? ''),
        ];
        
        // Update metadata
        foreach ($meta_fields as $meta_key => $meta_value) {
            if ($meta_key === 'skills') {
                update_post_meta($post_id, $meta_key, maybe_serialize($meta_value));
            } else {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        
        // Update post title with learner name
        if (!empty($meta_fields['first_name']) && !empty($meta_fields['last_name'])) {
            $learner_name = $meta_fields['first_name'] . ' ' . $meta_fields['last_name'];
            
            // Update post title without triggering infinite loop
            remove_action('save_post_cddu_learner', [$this, 'save_learner_meta'], 10);
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $learner_name
            ]);
            add_action('save_post_cddu_learner', [$this, 'save_learner_meta'], 10, 2);
        }
        
        // Update the serialized learner data for compatibility
        $updated_post = get_post($post_id);
        $learner_data = array_merge($meta_fields, [
            'title' => $updated_post->post_title,
        ]);
        
        update_post_meta($post_id, 'learner', maybe_serialize($learner_data));
        update_post_meta($post_id, 'learner_status', $meta_fields['status']);
        update_post_meta($post_id, 'learner_level', $meta_fields['level']);
        update_post_meta($post_id, 'updated_date', current_time('mysql'));
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
        global $post_type, $post;
        
        // Enqueue for learner editing on post.php
        if (in_array($hook, ['post.php', 'post-new.php']) && $post_type === 'cddu_learner') {
            wp_enqueue_style(
                'cddu-learner-manager',
                CDDU_MNGR_URL . 'assets/css/learner-manager.css',
                [],
                CDDU_MNGR_VERSION
            );

            wp_enqueue_script(
                'cddu-learner-manager',
                CDDU_MNGR_URL . 'assets/js/learner-manager.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
            
            wp_localize_script('cddu-learner-manager', 'cddu_learner_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cddu_learner_nonce'),
                'is_edit_page' => true,
                'strings' => [
                    'learner_updated' => __('Learner updated successfully', 'wp-cddu-manager'),
                    'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                    'loading' => __('Loading...', 'wp-cddu-manager'),
                    'confirm_delete' => __('Are you sure you want to delete this learner?', 'wp-cddu-manager'),
                    'learner_created' => __('Learner created successfully', 'wp-cddu-manager'),
                    'learner_deleted' => __('Learner deleted successfully', 'wp-cddu-manager'),
                ]
            ]);
        }
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
    
    /**
     * Render personal information metabox
     */
    public function render_personal_information_metabox($post): void {
        // Add nonce for security
        wp_nonce_field('cddu_learner_nonce', 'cddu_learner_nonce');
        
        // Get existing data
        $existing_learner_data = $this->get_learner_data($post);
        
        // Include the personal information template
        include CDDU_MNGR_PATH . 'templates/admin/learners/metaboxes/personal-information.php';
    }
    
    /**
     * Render address information metabox
     */
    public function render_address_information_metabox($post): void {
        // Get existing data
        $existing_learner_data = $this->get_learner_data($post);
        
        // Include the address information template
        include CDDU_MNGR_PATH . 'templates/admin/learners/metaboxes/address-information.php';
    }
    
    /**
     * Render contact information metabox
     */
    public function render_contact_information_metabox($post): void {
        // Get existing data
        $existing_learner_data = $this->get_learner_data($post);
        
        // Include the contact information template
        include CDDU_MNGR_PATH . 'templates/admin/learners/metaboxes/contact-information.php';
    }
    
    /**
     * Render academic information metabox
     */
    public function render_academic_information_metabox($post): void {
        // Get existing data
        $existing_learner_data = $this->get_learner_data($post);
        
        // Include the academic information template
        include CDDU_MNGR_PATH . 'templates/admin/learners/metaboxes/academic-information.php';
    }
    
    /**
     * Render additional notes metabox
     */
    public function render_additional_notes_metabox($post): void {
        // Get existing data
        $existing_learner_data = $this->get_learner_data($post);
        
        // Include the additional notes template
        include CDDU_MNGR_PATH . 'templates/admin/learners/metaboxes/additional-notes.php';
    }
    
    /**
     * Get learner data for a post
     */
    private function get_learner_data($post): array {
        $learner_id = $post->ID;
        $editing_mode = $learner_id > 0;
        
        // For new learners, initialize empty data structure
        $existing_learner_data = [
            'first_name' => '',
            'last_name' => '',
            'birth_date' => '',
            'birth_place' => '',
            'nationality' => '',
            'social_security' => '',
            'gender' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'country' => '',
            'email' => '',
            'phone' => '',
            'mobile_phone' => '',
            'emergency_contact' => '',
            'emergency_phone' => '',
            'emergency_relationship' => '',
            'level' => '',
            'diploma' => '',
            'specialization' => '',
            'previous_experience' => '',
            'skills' => [],
            'status' => 'active',
            'notes' => '',
            'medical_notes' => '',
            'special_requirements' => '',
        ];
        
        // If editing existing learner, load actual data
        if ($editing_mode) {
            $existing_learner_data = [
                'first_name' => get_post_meta($learner_id, 'first_name', true),
                'last_name' => get_post_meta($learner_id, 'last_name', true),
                'birth_date' => get_post_meta($learner_id, 'birth_date', true),
                'birth_place' => get_post_meta($learner_id, 'birth_place', true),
                'nationality' => get_post_meta($learner_id, 'nationality', true),
                'social_security' => get_post_meta($learner_id, 'social_security', true),
                'gender' => get_post_meta($learner_id, 'gender', true),
                'address' => get_post_meta($learner_id, 'address', true),
                'city' => get_post_meta($learner_id, 'city', true),
                'postal_code' => get_post_meta($learner_id, 'postal_code', true),
                'country' => get_post_meta($learner_id, 'country', true),
                'email' => get_post_meta($learner_id, 'email', true),
                'phone' => get_post_meta($learner_id, 'phone', true),
                'mobile_phone' => get_post_meta($learner_id, 'mobile_phone', true),
                'emergency_contact' => get_post_meta($learner_id, 'emergency_contact', true),
                'emergency_phone' => get_post_meta($learner_id, 'emergency_phone', true),
                'emergency_relationship' => get_post_meta($learner_id, 'emergency_relationship', true),
                'level' => get_post_meta($learner_id, 'level', true),
                'diploma' => get_post_meta($learner_id, 'diploma', true),
                'specialization' => get_post_meta($learner_id, 'specialization', true),
                'previous_experience' => get_post_meta($learner_id, 'previous_experience', true),
                'skills' => maybe_unserialize(get_post_meta($learner_id, 'skills', true)) ?: [],
                'status' => get_post_meta($learner_id, 'status', true) ?: 'active',
                'notes' => get_post_meta($learner_id, 'notes', true),
                'medical_notes' => get_post_meta($learner_id, 'medical_notes', true),
                'special_requirements' => get_post_meta($learner_id, 'special_requirements', true),
            ];
        }
        
        return $existing_learner_data;
    }
}
