<?php

namespace CDDU_Manager\Admin;

class LearnerFormManager
{
    private static ?self $instance = null;

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Register save hook globally - must be available on all admin pages
        add_action('save_post_cddu_learner', [$this, 'save_learner_data'], 10, 2);
        
        add_action('admin_init', [$this, 'init']);
        // Remove AJAX handlers as we now use native form submission
    }

    public function init(): void
    {
        // Only hook into cddu_learner post type pages
        if (!$this->is_learner_edit_page()) {
            return;
        }

        // Remove the default metaboxes for learners
        add_action('add_meta_boxes', [$this, 'remove_default_metaboxes'], 999);
        
        // Add our custom form rendering
        add_action('edit_form_after_title', [$this, 'render_custom_form']);
        
        // Enqueue custom styles and scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Hide unnecessary elements
        add_action('admin_head', [$this, 'hide_default_elements']);
    }

    private function is_learner_edit_page(): bool
    {
        global $pagenow, $post_type;
        
        return (
            ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'cddu_learner') ||
            ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'cddu_learner')
        );
    }

    public function remove_default_metaboxes(): void
    {
        // Remove the default learner metaboxes
        remove_meta_box('cddu_learner_details', 'cddu_learner', 'normal');
        remove_meta_box('cddu_learner_contact', 'cddu_learner', 'side');
        
        // Also remove other default metaboxes that aren't needed
        remove_meta_box('slugdiv', 'cddu_learner', 'normal');
        remove_meta_box('postcustom', 'cddu_learner', 'normal');
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style(
            'cddu-learner-form',
            CDDU_MNGR_URL . 'assets/css/learner-form.css',
            [],
            CDDU_MNGR_VERSION
        );

        wp_enqueue_script(
            'cddu-learner-form',
            CDDU_MNGR_URL . 'assets/js/learner-form.js',
            ['jquery'],
            CDDU_MNGR_VERSION,
            true
        );

        wp_localize_script('cddu-learner-form', 'cdduLearnerForm', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cddu_learner_form_nonce'),
            'strings' => [
                'validationErrors' => __('Please correct the errors below:', 'wp-cddu-manager'),
                'requiredField' => __('This field is required.', 'wp-cddu-manager'),
                'invalidEmail' => __('Please enter a valid email address.', 'wp-cddu-manager'),
                'invalidPhone' => __('Please enter a valid phone number.', 'wp-cddu-manager'),
                'saving' => __('Saving...', 'wp-cddu-manager'),
                'savingDraft' => __('Saving Draft...', 'wp-cddu-manager'),
                'saved' => __('Saved!', 'wp-cddu-manager'),
            ]
        ]);
    }

    public function hide_default_elements(): void
    {
        echo '<style>
            #titlediv { display: none !important; }
            .postdivrich { display: none !important; }
            #wp-content-editor-container { display: none !important; }
            .page-title-action { display: none !important; }
        </style>';
    }

    public function customize_submit_box(): void
    {
        global $post;
        
        if ($post && $post->post_type === 'cddu_learner') {
            echo '<div class="misc-pub-section">
                <span id="learner-status-display">';
            
            if ($post->ID) {
                $learner_data = $this->get_learner_data($post->ID);
                $status = $learner_data['status'] ?? 'active';
                $status_labels = [
                    'active' => __('Active', 'wp-cddu-manager'),
                    'inactive' => __('Inactive', 'wp-cddu-manager'),
                    'completed' => __('Completed', 'wp-cddu-manager'),
                    'dropped' => __('Dropped', 'wp-cddu-manager'),
                ];
                echo sprintf(__('Status: %s', 'wp-cddu-manager'), $status_labels[$status] ?? $status);
            } else {
                echo __('Status: New Learner', 'wp-cddu-manager');
            }
            
            echo '</span>
            </div>';
        }
    }

    public function render_custom_form(): void
    {
        global $post;
        
        if (!$post || $post->post_type !== 'cddu_learner') {
            return;
        }

        $learner_data = $this->get_learner_data($post->ID);
        
        wp_nonce_field('cddu_learner_form_nonce', 'cddu_learner_form_nonce');
        
        include CDDU_MNGR_PATH . 'templates/admin/learner-form.php';
    }

    private function get_learner_data(int $post_id): array
    {
        if (!$post_id) {
            return $this->get_default_learner_data();
        }

        // Get data from meta (for backward compatibility)
        $meta_data = get_post_meta($post_id, 'learner', true);
        $learner_data = maybe_unserialize($meta_data) ?: [];
        
        // Merge with default values
        return array_merge($this->get_default_learner_data(), $learner_data);
    }

    private function get_default_learner_data(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'birth_date' => '',
            'social_security' => '',
            'address' => '',
            'postal_code' => '',
            'city' => '',
            'country' => 'France',
            'email' => '',
            'phone' => '',
            'mobile_phone' => '',
            'emergency_contact' => '',
            'emergency_phone' => '',
            'level' => '',
            'status' => 'active',
            'notes' => '',
            'enrollment_date' => '',
            'expected_completion_date' => '',
        ];
    }

    public function save_learner_data(int $post_id, \WP_Post $post): void
    {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if this is our form submission
        if (!isset($_POST['cddu_learner_form_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['cddu_learner_form_nonce'], 'cddu_learner_form_nonce')) {
            return;
        }

        // Get and sanitize form data
        $learner_data = $this->sanitize_learner_data($_POST);
        
        // Validate required fields for published posts only
        $validation_errors = [];
        if ($post->post_status === 'publish') {
            $validation_errors = $this->validate_learner_data($learner_data);
        }
        
        if (!empty($validation_errors)) {
            // Store errors in session to display them
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['cddu_learner_validation_errors'] = $validation_errors;
            
            // Prevent the post from being saved by setting it back to draft
            remove_action('save_post_cddu_learner', [$this, 'save_learner_data'], 10);
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'draft'
            ]);
            add_action('save_post_cddu_learner', [$this, 'save_learner_data'], 10, 2);
            
            return;
        }

        // Save the data
        update_post_meta($post_id, 'learner', maybe_serialize($learner_data));
        
        // Update post title with learner name
        if (!empty($learner_data['first_name']) && !empty($learner_data['last_name'])) {
            $learner_name = $learner_data['first_name'] . ' ' . $learner_data['last_name'];
            remove_action('save_post_cddu_learner', [$this, 'save_learner_data'], 10);
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $learner_name,
                'post_content' => $learner_data['notes'] ?? '',
            ]);
            add_action('save_post_cddu_learner', [$this, 'save_learner_data'], 10, 2);
        }

        // Clear any validation errors
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['cddu_learner_validation_errors']);
    }

    private function sanitize_learner_data(array $post_data): array
    {
        $sanitized = [];
        
        $text_fields = [
            'first_name', 'last_name', 'postal_code', 
            'city', 'country', 'phone', 'mobile_phone', 'emergency_contact', 
            'emergency_phone', 'level', 'status'
        ];
        
        foreach ($text_fields as $field) {
            $sanitized[$field] = sanitize_text_field($post_data[$field] ?? '');
        }
        
        // Special handling for social security number - normalize format
        $sanitized['social_security'] = $this->normalize_social_security_number($post_data['social_security'] ?? '');
        
        $textarea_fields = ['address', 'notes'];
        foreach ($textarea_fields as $field) {
            $sanitized[$field] = sanitize_textarea_field($post_data[$field] ?? '');
        }
        
        $date_fields = ['birth_date', 'enrollment_date', 'expected_completion_date'];
        foreach ($date_fields as $field) {
            $sanitized[$field] = sanitize_text_field($post_data[$field] ?? '');
        }
        
        $sanitized['email'] = sanitize_email($post_data['email'] ?? '');
        
        return $sanitized;
    }

    private function validate_learner_data(array $data): array
    {
        $errors = [];
        
        // Required fields
        $required_fields = [
            'first_name' => __('First Name', 'wp-cddu-manager'),
            'last_name' => __('Last Name', 'wp-cddu-manager'),
        ];
        
        foreach ($required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[$field] = sprintf(__('%s is required.', 'wp-cddu-manager'), $label);
            }
        }
        
        // Social Security Number validation
        if (!empty($data['social_security'])) {
            if (!$this->is_valid_social_security_number($data['social_security'])) {
                $errors['social_security'] = __('Please enter a valid social security number.', 'wp-cddu-manager');
            } elseif (!$this->is_unique_social_security_number($data['social_security'], get_the_ID())) {
                $errors['social_security'] = __('This social security number is already in use by another learner.', 'wp-cddu-manager');
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors['email'] = __('Please enter a valid email address.', 'wp-cddu-manager');
        }
        
        // Notes character limit validation
        if (!empty($data['notes']) && strlen($data['notes']) > 500) {
            $errors['notes'] = __('Additional notes must not exceed 500 characters.', 'wp-cddu-manager');
        }
        
        // Date validation
        $date_fields = ['birth_date', 'enrollment_date', 'expected_completion_date'];
        foreach ($date_fields as $field) {
            if (!empty($data[$field])) {
                $date = \DateTime::createFromFormat('Y-m-d', $data[$field]);
                if (!$date || $date->format('Y-m-d') !== $data[$field]) {
                    $errors[$field] = __('Please enter a valid date.', 'wp-cddu-manager');
                }
            }
        }
        
        return $errors;
    }

    public function get_validation_errors(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['cddu_learner_validation_errors'] ?? [];
    }

    /**
     * Normalize social security number format
     * Removes extra spaces and formats consistently
     */
    private function normalize_social_security_number(string $ssn): string
    {
        if (empty($ssn)) {
            return '';
        }
        
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $ssn);
        
        // If we have 13 or 15 digits, format with spaces
        if (strlen($cleaned) === 13) {
            return substr($cleaned, 0, 1) . ' ' . 
                   substr($cleaned, 1, 2) . ' ' . 
                   substr($cleaned, 3, 2) . ' ' . 
                   substr($cleaned, 5, 2) . ' ' . 
                   substr($cleaned, 7, 3) . ' ' . 
                   substr($cleaned, 10, 3);
        } elseif (strlen($cleaned) === 15) {
            return substr($cleaned, 0, 1) . ' ' . 
                   substr($cleaned, 1, 2) . ' ' . 
                   substr($cleaned, 3, 2) . ' ' . 
                   substr($cleaned, 5, 2) . ' ' . 
                   substr($cleaned, 7, 3) . ' ' . 
                   substr($cleaned, 10, 3) . ' ' . 
                   substr($cleaned, 13, 2);
        }
        
        // Return as-is if format doesn't match expected lengths
        return sanitize_text_field($ssn);
    }

    /**
     * Validate French social security number format
     * Format: 1 23 45 67 890 123 45 or 1234567890123
     */
    private function is_valid_social_security_number(string $ssn): bool
    {
        // Remove spaces and non-digit characters
        $cleaned_ssn = preg_replace('/[^0-9]/', '', $ssn);
        
        // Check if it has exactly 13 or 15 digits (13 for mainland France, 15 with key)
        if (strlen($cleaned_ssn) !== 13 && strlen($cleaned_ssn) !== 15) {
            return false;
        }
        
        // Take only first 13 digits for validation
        $ssn_13 = substr($cleaned_ssn, 0, 13);
        
        // Basic format validation
        if (!preg_match('/^[12][0-9]{12}$/', $ssn_13)) {
            return false;
        }
        
        // Extract components
        $gender = (int)substr($ssn_13, 0, 1);
        $year = (int)substr($ssn_13, 1, 2);
        $month = (int)substr($ssn_13, 3, 2);
        $department = substr($ssn_13, 5, 2);
        
        // Validate gender (1 = male, 2 = female)
        if ($gender !== 1 && $gender !== 2) {
            return false;
        }
        
        // Validate month (01-12 or special codes 20 for unknown month)
        if (($month < 1 || $month > 12) && $month !== 20) {
            return false;
        }
        
        // Validate department code (basic check)
        if (!preg_match('/^[0-9A-B]{2}$/', $department)) {
            return false;
        }
        
        // If we have 15 digits, validate the key
        if (strlen($cleaned_ssn) === 15) {
            $key = (int)substr($cleaned_ssn, 13, 2);
            $calculated_key = 97 - ((int)$ssn_13 % 97);
            
            if ($key !== $calculated_key) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if social security number is unique among learners
     */
    private function is_unique_social_security_number(string $ssn, ?int $exclude_post_id = null): bool
    {
        // Remove spaces and format consistently
        $cleaned_ssn = preg_replace('/[^0-9]/', '', $ssn);
        
        // Query all learners
        $query_args = [
            'post_type' => 'cddu_learner',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_query' => [
                [
                    'key' => 'learner',
                    'value' => '',
                    'compare' => '!='
                ]
            ]
        ];
        
        // Exclude current post if editing
        if ($exclude_post_id) {
            $query_args['post__not_in'] = [$exclude_post_id];
        }
        
        $learners = get_posts($query_args);
        
        foreach ($learners as $learner) {
            $learner_data = get_post_meta($learner->ID, 'learner', true);
            $learner_data = maybe_unserialize($learner_data);
            
            if (is_array($learner_data) && !empty($learner_data['social_security'])) {
                $existing_ssn = preg_replace('/[^0-9]/', '', $learner_data['social_security']);
                
                // Compare cleaned versions
                if ($cleaned_ssn === $existing_ssn) {
                    return false; // Not unique
                }
            }
        }
        
        return true; // Unique
    }
}
