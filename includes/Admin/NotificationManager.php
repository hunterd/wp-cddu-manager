<?php
namespace CDDU_Manager\Admin;

class NotificationManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_get_notification_data', [$this, 'ajax_get_notification_data']);
        add_action('wp_ajax_cddu_mark_notification_read', [$this, 'ajax_mark_notification_read']);
        
        // Hook to replace notification edit interface
        add_action('add_meta_boxes', [$this, 'add_notification_edit_metabox']);
        add_action('add_meta_boxes', [$this, 'remove_default_metaboxes'], 999);
    }

    /**
     * Register notification post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_notification', [
            'label' => __('Notifications', 'wp-cddu-manager'),
            'labels' => [
                'name' => __('Notifications', 'wp-cddu-manager'),
                'singular_name' => __('Notification', 'wp-cddu-manager'),
                'add_new' => __('Add New Notification', 'wp-cddu-manager'),
                'add_new_item' => __('Add New Notification', 'wp-cddu-manager'),
                'edit_item' => __('Edit Notification', 'wp-cddu-manager'),
                'new_item' => __('New Notification', 'wp-cddu-manager'),
                'view_item' => __('View Notification', 'wp-cddu-manager'),
                'search_items' => __('Search Notifications', 'wp-cddu-manager'),
                'not_found' => __('No notifications found', 'wp-cddu-manager'),
                'not_found_in_trash' => __('No notifications found in trash', 'wp-cddu-manager'),
                'all_items' => __('All Notifications', 'wp-cddu-manager'),
                'menu_name' => __('Notifications', 'wp-cddu-manager'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-warning',
            'supports' => ['title', 'editor', 'custom-fields'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'capabilities' => [
                'create_posts' => 'cddu_manage',
            ],
        ]);
    }

    public function add_admin_menu(): void {
        // Notification functionality can be added here if needed
    }

    public function enqueue_scripts($hook): void {
        global $post_type, $post;
        
        // Enqueue for notification editing on post.php
        if (in_array($hook, ['post.php', 'post-new.php']) && $post_type === 'cddu_notification') {
            wp_enqueue_style(
                'cddu-notification-manager',
                CDDU_MNGR_URL . 'assets/css/admin-metaboxes.css',
                [],
                CDDU_MNGR_VERSION
            );

            wp_enqueue_script(
                'cddu-notification-manager',
                CDDU_MNGR_URL . 'assets/js/admin-organization.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
            
            wp_localize_script('cddu-notification-manager', 'cddu_notification_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cddu_notification_nonce'),
                'is_edit_page' => true,
                'strings' => [
                    'notification_updated' => __('Notification updated successfully', 'wp-cddu-manager'),
                    'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                    'loading' => __('Loading...', 'wp-cddu-manager'),
                ]
            ]);
        }
    }

    public function ajax_get_notification_data(): void {
        check_ajax_referer('cddu_notification_nonce', 'nonce');
        
        $notification_id = intval($_POST['notification_id'] ?? 0);
        if (!$notification_id) {
            wp_send_json_error(['message' => __('Invalid notification ID', 'wp-cddu-manager')]);
        }
        
        $notification_post = get_post($notification_id);
        if (!$notification_post || $notification_post->post_type !== 'cddu_notification') {
            wp_send_json_error(['message' => __('Notification not found', 'wp-cddu-manager')]);
        }
        
        $notification_data = [
            'notification_id' => $notification_id,
            'title' => $notification_post->post_title,
            'content' => $notification_post->post_content,
            'recipient_id' => get_post_meta($notification_id, 'recipient_id', true),
            'notification_type' => get_post_meta($notification_id, 'notification_type', true),
            'priority' => get_post_meta($notification_id, 'priority', true),
            'read_status' => get_post_meta($notification_id, 'read_status', true),
            'related_post_id' => get_post_meta($notification_id, 'related_post_id', true),
        ];
        
        wp_send_json_success(['notification_data' => $notification_data]);
    }

    public function ajax_mark_notification_read(): void {
        check_ajax_referer('cddu_notification_nonce', 'nonce');
        
        $notification_id = intval($_POST['notification_id'] ?? 0);
        if (!$notification_id) {
            wp_send_json_error(['message' => __('Invalid notification ID', 'wp-cddu-manager')]);
        }
        
        update_post_meta($notification_id, 'read_status', 'read');
        update_post_meta($notification_id, 'read_date', current_time('mysql'));
        
        wp_send_json_success(['message' => __('Notification marked as read', 'wp-cddu-manager')]);
    }
    
    /**
     * Add notification edit metabox
     */
    public function add_notification_edit_metabox(): void {
        // Notification Details metabox
        add_meta_box(
            'cddu-notification-details',
            __('Notification Details', 'wp-cddu-manager'),
            [$this, 'render_notification_details_metabox'],
            'cddu_notification',
            'normal',
            'high'
        );
    }
    
    /**
     * Remove default WordPress metaboxes for notifications
     */
    public function remove_default_metaboxes(): void {
        // Remove custom fields metabox
        remove_meta_box('postcustom', 'cddu_notification', 'normal');
        remove_meta_box('postcustom', 'cddu_notification', 'advanced');
        
        // Remove slug metabox
        remove_meta_box('slugdiv', 'cddu_notification', 'normal');
        remove_meta_box('slugdiv', 'cddu_notification', 'advanced');
    }
    
    /**
     * Render notification details metabox
     */
    public function render_notification_details_metabox($post): void {
        // Add nonce for security
        wp_nonce_field('cddu_notification_nonce', 'cddu_notification_nonce');
        
        // Get existing values
        $recipient_id = get_post_meta($post->ID, 'recipient_id', true);
        $notification_type = get_post_meta($post->ID, 'notification_type', true);
        $priority = get_post_meta($post->ID, 'priority', true);
        $read_status = get_post_meta($post->ID, 'read_status', true);
        $related_post_id = get_post_meta($post->ID, 'related_post_id', true);
        
        // Get users for recipient selection
        $users = get_users(['capability' => 'read']);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="recipient_id"><?php _e('Recipient', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="recipient_id" name="recipient_id" style="width: 100%;">
                        <option value=""><?php _e('Select Recipient', 'wp-cddu-manager'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($recipient_id, $user->ID); ?>>
                                <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="notification_type"><?php _e('Type', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="notification_type" name="notification_type">
                        <option value="info" <?php selected($notification_type, 'info'); ?>><?php _e('Information', 'wp-cddu-manager'); ?></option>
                        <option value="warning" <?php selected($notification_type, 'warning'); ?>><?php _e('Warning', 'wp-cddu-manager'); ?></option>
                        <option value="error" <?php selected($notification_type, 'error'); ?>><?php _e('Error', 'wp-cddu-manager'); ?></option>
                        <option value="success" <?php selected($notification_type, 'success'); ?>><?php _e('Success', 'wp-cddu-manager'); ?></option>
                        <option value="reminder" <?php selected($notification_type, 'reminder'); ?>><?php _e('Reminder', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="priority"><?php _e('Priority', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="priority" name="priority">
                        <option value="low" <?php selected($priority, 'low'); ?>><?php _e('Low', 'wp-cddu-manager'); ?></option>
                        <option value="medium" <?php selected($priority, 'medium'); ?>><?php _e('Medium', 'wp-cddu-manager'); ?></option>
                        <option value="high" <?php selected($priority, 'high'); ?>><?php _e('High', 'wp-cddu-manager'); ?></option>
                        <option value="urgent" <?php selected($priority, 'urgent'); ?>><?php _e('Urgent', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="read_status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="read_status" name="read_status">
                        <option value="unread" <?php selected($read_status, 'unread'); ?>><?php _e('Unread', 'wp-cddu-manager'); ?></option>
                        <option value="read" <?php selected($read_status, 'read'); ?>><?php _e('Read', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="related_post_id"><?php _e('Related Post ID', 'wp-cddu-manager'); ?></label></th>
                <td><input type="number" id="related_post_id" name="related_post_id" value="<?php echo esc_attr($related_post_id); ?>" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save notification meta data
     */
    public function save_notification_meta($post_id, $post): void {
        // Verify nonce
        if (!isset($_POST['cddu_notification_nonce']) || !wp_verify_nonce($_POST['cddu_notification_nonce'], 'cddu_notification_nonce')) {
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
        
        // Save notification meta data from metaboxes
        $meta_fields = [
            'recipient_id' => intval($_POST['recipient_id'] ?? 0),
            'notification_type' => sanitize_text_field($_POST['notification_type'] ?? 'info'),
            'priority' => sanitize_text_field($_POST['priority'] ?? 'medium'),
            'read_status' => sanitize_text_field($_POST['read_status'] ?? 'unread'),
            'related_post_id' => intval($_POST['related_post_id'] ?? 0),
        ];
        
        // Update metadata
        foreach ($meta_fields as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }
        
        update_post_meta($post_id, 'updated_date', current_time('mysql'));
    }
}
