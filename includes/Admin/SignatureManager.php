<?php
namespace CDDU_Manager\Admin;

class SignatureManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_get_signature_data', [$this, 'ajax_get_signature_data']);
        add_action('wp_ajax_cddu_send_signature_request', [$this, 'ajax_send_signature_request']);
        
        // Hook to replace signature edit interface
        add_action('add_meta_boxes', [$this, 'add_signature_edit_metabox']);
        add_action('add_meta_boxes', [$this, 'remove_default_metaboxes'], 999);
    }

    /**
     * Register signature post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_signature', [
            'label' => __('Signature Requests', 'wp-cddu-manager'),
            'labels' => [
                'name' => __('Signature Requests', 'wp-cddu-manager'),
                'singular_name' => __('Signature Request', 'wp-cddu-manager'),
                'add_new' => __('Add New Signature Request', 'wp-cddu-manager'),
                'add_new_item' => __('Add New Signature Request', 'wp-cddu-manager'),
                'edit_item' => __('Edit Signature Request', 'wp-cddu-manager'),
                'new_item' => __('New Signature Request', 'wp-cddu-manager'),
                'view_item' => __('View Signature Request', 'wp-cddu-manager'),
                'search_items' => __('Search Signature Requests', 'wp-cddu-manager'),
                'not_found' => __('No signature requests found', 'wp-cddu-manager'),
                'not_found_in_trash' => __('No signature requests found in trash', 'wp-cddu-manager'),
                'all_items' => __('All Signature Requests', 'wp-cddu-manager'),
                'menu_name' => __('Signature Requests', 'wp-cddu-manager'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-edit-page',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'capabilities' => [
                'create_posts' => 'cddu_manage',
            ],
        ]);
    }

    public function add_admin_menu(): void {
        // Signature functionality can be added here if needed
    }

    public function enqueue_scripts($hook): void {
        global $post_type, $post;
        
        // Enqueue for signature editing on post.php
        if (in_array($hook, ['post.php', 'post-new.php']) && $post_type === 'cddu_signature') {
            wp_enqueue_style(
                'cddu-signature-manager',
                CDDU_MNGR_URL . 'assets/css/admin-metaboxes.css',
                [],
                CDDU_MNGR_VERSION
            );

            wp_enqueue_script(
                'cddu-signature-manager',
                CDDU_MNGR_URL . 'assets/js/admin-organization.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
            
            wp_localize_script('cddu-signature-manager', 'cddu_signature_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cddu_signature_nonce'),
                'is_edit_page' => true,
                'strings' => [
                    'signature_updated' => __('Signature request updated successfully', 'wp-cddu-manager'),
                    'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                    'loading' => __('Loading...', 'wp-cddu-manager'),
                ]
            ]);
        }
    }

    public function ajax_get_signature_data(): void {
        check_ajax_referer('cddu_signature_nonce', 'nonce');
        
        $signature_id = intval($_POST['signature_id'] ?? 0);
        if (!$signature_id) {
            wp_send_json_error(['message' => __('Invalid signature ID', 'wp-cddu-manager')]);
        }
        
        $signature_post = get_post($signature_id);
        if (!$signature_post || $signature_post->post_type !== 'cddu_signature') {
            wp_send_json_error(['message' => __('Signature request not found', 'wp-cddu-manager')]);
        }
        
        $signature_data = [
            'signature_id' => $signature_id,
            'title' => $signature_post->post_title,
            'document_type' => get_post_meta($signature_id, 'document_type', true),
            'related_post_id' => get_post_meta($signature_id, 'related_post_id', true),
            'signer_email' => get_post_meta($signature_id, 'signer_email', true),
            'signer_name' => get_post_meta($signature_id, 'signer_name', true),
            'signature_status' => get_post_meta($signature_id, 'signature_status', true),
            'provider' => get_post_meta($signature_id, 'provider', true),
            'provider_request_id' => get_post_meta($signature_id, 'provider_request_id', true),
            'signature_url' => get_post_meta($signature_id, 'signature_url', true),
            'expires_at' => get_post_meta($signature_id, 'expires_at', true),
        ];
        
        wp_send_json_success(['signature_data' => $signature_data]);
    }

    public function ajax_send_signature_request(): void {
        check_ajax_referer('cddu_signature_nonce', 'nonce');
        
        $signature_id = intval($_POST['signature_id'] ?? 0);
        if (!$signature_id) {
            wp_send_json_error(['message' => __('Invalid signature ID', 'wp-cddu-manager')]);
        }
        
        try {
            // Here you would integrate with signature providers like DocuSign or YouSign
            // For now, we'll just update the status
            update_post_meta($signature_id, 'signature_status', 'sent');
            update_post_meta($signature_id, 'sent_date', current_time('mysql'));
            
            wp_send_json_success(['message' => __('Signature request sent successfully', 'wp-cddu-manager')]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Add signature edit metabox
     */
    public function add_signature_edit_metabox(): void {
        // Signature Details metabox
        add_meta_box(
            'cddu-signature-details',
            __('Signature Request Details', 'wp-cddu-manager'),
            [$this, 'render_signature_details_metabox'],
            'cddu_signature',
            'normal',
            'high'
        );
        
        // Signature Tracking metabox
        add_meta_box(
            'cddu-signature-tracking',
            __('Signature Tracking', 'wp-cddu-manager'),
            [$this, 'render_signature_tracking_metabox'],
            'cddu_signature',
            'side',
            'default'
        );
    }
    
    /**
     * Remove default WordPress metaboxes for signatures
     */
    public function remove_default_metaboxes(): void {
        // Remove custom fields metabox
        remove_meta_box('postcustom', 'cddu_signature', 'normal');
        remove_meta_box('postcustom', 'cddu_signature', 'advanced');
        
        // Remove slug metabox
        remove_meta_box('slugdiv', 'cddu_signature', 'normal');
        remove_meta_box('slugdiv', 'cddu_signature', 'advanced');
    }
    
    /**
     * Render signature details metabox
     */
    public function render_signature_details_metabox($post): void {
        // Add nonce for security
        wp_nonce_field('cddu_signature_nonce', 'cddu_signature_nonce');
        
        // Get existing values
        $document_type = get_post_meta($post->ID, 'document_type', true);
        $related_post_id = get_post_meta($post->ID, 'related_post_id', true);
        $signer_email = get_post_meta($post->ID, 'signer_email', true);
        $signer_name = get_post_meta($post->ID, 'signer_name', true);
        $provider = get_post_meta($post->ID, 'provider', true);
        $signature_url = get_post_meta($post->ID, 'signature_url', true);
        $expires_at = get_post_meta($post->ID, 'expires_at', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="document_type"><?php _e('Document Type', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="document_type" name="document_type">
                        <option value="contract" <?php selected($document_type, 'contract'); ?>><?php _e('Contract', 'wp-cddu-manager'); ?></option>
                        <option value="addendum" <?php selected($document_type, 'addendum'); ?>><?php _e('Addendum', 'wp-cddu-manager'); ?></option>
                        <option value="timesheet" <?php selected($document_type, 'timesheet'); ?>><?php _e('Timesheet', 'wp-cddu-manager'); ?></option>
                        <option value="other" <?php selected($document_type, 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="related_post_id"><?php _e('Related Document ID', 'wp-cddu-manager'); ?></label></th>
                <td><input type="number" id="related_post_id" name="related_post_id" value="<?php echo esc_attr($related_post_id); ?>" /></td>
            </tr>
            <tr>
                <th><label for="signer_name"><?php _e('Signer Name', 'wp-cddu-manager'); ?></label></th>
                <td><input type="text" id="signer_name" name="signer_name" value="<?php echo esc_attr($signer_name); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label for="signer_email"><?php _e('Signer Email', 'wp-cddu-manager'); ?></label></th>
                <td><input type="email" id="signer_email" name="signer_email" value="<?php echo esc_attr($signer_email); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label for="provider"><?php _e('Signature Provider', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="provider" name="provider">
                        <option value="docusign" <?php selected($provider, 'docusign'); ?>><?php _e('DocuSign', 'wp-cddu-manager'); ?></option>
                        <option value="yousign" <?php selected($provider, 'yousign'); ?>><?php _e('YouSign', 'wp-cddu-manager'); ?></option>
                        <option value="manual" <?php selected($provider, 'manual'); ?>><?php _e('Manual', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="signature_url"><?php _e('Signature URL', 'wp-cddu-manager'); ?></label></th>
                <td><input type="url" id="signature_url" name="signature_url" value="<?php echo esc_attr($signature_url); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label for="expires_at"><?php _e('Expires At', 'wp-cddu-manager'); ?></label></th>
                <td><input type="datetime-local" id="expires_at" name="expires_at" value="<?php echo esc_attr($expires_at); ?>" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render signature tracking metabox
     */
    public function render_signature_tracking_metabox($post): void {
        $signature_status = get_post_meta($post->ID, 'signature_status', true);
        $provider_request_id = get_post_meta($post->ID, 'provider_request_id', true);
        $sent_date = get_post_meta($post->ID, 'sent_date', true);
        $signed_date = get_post_meta($post->ID, 'signed_date', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="signature_status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="signature_status" name="signature_status">
                        <option value="draft" <?php selected($signature_status, 'draft'); ?>><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                        <option value="sent" <?php selected($signature_status, 'sent'); ?>><?php _e('Sent', 'wp-cddu-manager'); ?></option>
                        <option value="viewed" <?php selected($signature_status, 'viewed'); ?>><?php _e('Viewed', 'wp-cddu-manager'); ?></option>
                        <option value="signed" <?php selected($signature_status, 'signed'); ?>><?php _e('Signed', 'wp-cddu-manager'); ?></option>
                        <option value="cancelled" <?php selected($signature_status, 'cancelled'); ?>><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                        <option value="expired" <?php selected($signature_status, 'expired'); ?>><?php _e('Expired', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="provider_request_id"><?php _e('Provider Request ID', 'wp-cddu-manager'); ?></label></th>
                <td><input type="text" id="provider_request_id" name="provider_request_id" value="<?php echo esc_attr($provider_request_id); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <?php if ($sent_date): ?>
            <tr>
                <th><?php _e('Sent Date', 'wp-cddu-manager'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($sent_date))); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($signed_date): ?>
            <tr>
                <th><?php _e('Signed Date', 'wp-cddu-manager'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signed_date))); ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    /**
     * Save signature meta data
     */
    public function save_signature_meta($post_id, $post): void {
        // Verify nonce
        if (!isset($_POST['cddu_signature_nonce']) || !wp_verify_nonce($_POST['cddu_signature_nonce'], 'cddu_signature_nonce')) {
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
        
        // Save signature meta data from metaboxes
        $meta_fields = [
            'document_type' => sanitize_text_field($_POST['document_type'] ?? 'contract'),
            'related_post_id' => intval($_POST['related_post_id'] ?? 0),
            'signer_name' => sanitize_text_field($_POST['signer_name'] ?? ''),
            'signer_email' => sanitize_email($_POST['signer_email'] ?? ''),
            'provider' => sanitize_text_field($_POST['provider'] ?? 'docusign'),
            'signature_url' => esc_url_raw($_POST['signature_url'] ?? ''),
            'expires_at' => sanitize_text_field($_POST['expires_at'] ?? ''),
            'signature_status' => sanitize_text_field($_POST['signature_status'] ?? 'draft'),
            'provider_request_id' => sanitize_text_field($_POST['provider_request_id'] ?? ''),
        ];
        
        // Update metadata
        foreach ($meta_fields as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }
        
        update_post_meta($post_id, 'updated_date', current_time('mysql'));
    }
}
