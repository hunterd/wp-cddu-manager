<?php
namespace CDDU_Manager\Admin;

use CDDU_Manager\Calculations;

class MissionManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_get_mission_data', [$this, 'ajax_get_mission_data']);
        add_action('wp_ajax_cddu_get_missions_for_organization', [$this, 'ajax_get_missions_for_organization']);
        
        // Hook to replace mission edit interface
        add_action('add_meta_boxes', [$this, 'add_mission_edit_metabox']);
        add_action('add_meta_boxes', [$this, 'remove_default_metaboxes'], 999);
        add_action('admin_head-post.php', [$this, 'customize_mission_editor']);
        add_action('admin_head-post-new.php', [$this, 'customize_mission_editor']);
    }

    /**
     * Register mission post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_mission', [
            'label' => __('Missions', 'wp-cddu-manager'),
            'labels' => [
                'name' => __('Missions', 'wp-cddu-manager'),
                'singular_name' => __('Mission', 'wp-cddu-manager'),
                'add_new' => __('Add New Mission', 'wp-cddu-manager'),
                'add_new_item' => __('Add New Mission', 'wp-cddu-manager'),
                'edit_item' => __('Edit Mission', 'wp-cddu-manager'),
                'new_item' => __('New Mission', 'wp-cddu-manager'),
                'view_item' => __('View Mission', 'wp-cddu-manager'),
                'search_items' => __('Search Missions', 'wp-cddu-manager'),
                'not_found' => __('No missions found', 'wp-cddu-manager'),
                'not_found_in_trash' => __('No missions found in trash', 'wp-cddu-manager'),
                'all_items' => __('All Missions', 'wp-cddu-manager'),
                'menu_name' => __('Missions', 'wp-cddu-manager'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => ['title', 'custom-fields'], // Enable basic editing interface
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'capabilities' => [
                'create_posts' => 'cddu_manage', // Require custom capability to create
            ],
        ]);
    }

    public function add_admin_menu(): void {
        // Create mission functionality has been removed
        // Mission creation is now handled through WordPress native interface
    }

    /**
     * Remove the default "Add New Mission" submenu since we have a custom create mission page
     */
    public function remove_add_new_submenu(): void {
        global $submenu;
        
        // Remove "Add New Mission" submenu item
        if (isset($submenu['edit.php?post_type=cddu_mission'])) {
            foreach ($submenu['edit.php?post_type=cddu_mission'] as $key => $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'post-new.php?post_type=cddu_mission') {
                    unset($submenu['edit.php?post_type=cddu_mission'][$key]);
                    break;
                }
            }
        }
    }

    public function enqueue_scripts($hook): void {
        global $post_type, $post;
        
        // Enqueue for mission editing on post.php
        if (in_array($hook, ['post.php', 'post-new.php']) && $post_type === 'cddu_mission') {
            wp_enqueue_style(
                'cddu-mission-manager',
                CDDU_MNGR_URL . 'assets/css/mission-manager.css',
                [],
                CDDU_MNGR_VERSION
            );
            
            wp_enqueue_style(
                'cddu-mission-form',
                CDDU_MNGR_URL . 'assets/css/mission-form.css',
                [],
                CDDU_MNGR_VERSION
            );

            wp_enqueue_script(
                'cddu-mission-manager',
                CDDU_MNGR_URL . 'assets/js/mission-manager.js',
                ['jquery'],
                CDDU_MNGR_VERSION,
                true
            );
            
            wp_localize_script('cddu-mission-manager', 'cddu_mission_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cddu_mission_nonce'),
                'is_edit_page' => true,
                'strings' => [
                    'mission_updated' => __('Mission updated successfully', 'wp-cddu-manager'),
                    'validation_error' => __('Please fill in all required fields', 'wp-cddu-manager'),
                    'loading' => __('Loading...', 'wp-cddu-manager'),
                ]
            ]);
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
        ];
    }
    
    /**
     * Render mission details metabox
     */
    public function render_mission_details_metabox($post): void {
        // Add nonce for security
        wp_nonce_field('cddu_mission_nonce', 'cddu_mission_nonce');
        
        // Get existing values
        $organization_id = get_post_meta($post->ID, 'organization_id', true);
        $location = get_post_meta($post->ID, 'location', true);
        $start_date = get_post_meta($post->ID, 'start_date', true);
        $end_date = get_post_meta($post->ID, 'end_date', true);
        $total_hours = get_post_meta($post->ID, 'total_hours', true);
        $hourly_rate = get_post_meta($post->ID, 'hourly_rate', true);
        $mission_type = get_post_meta($post->ID, 'mission_type', true);
        $priority = get_post_meta($post->ID, 'priority', true);
        $mission_status = get_post_meta($post->ID, 'mission_status', true);
        
        // Get organizations
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="organization_id"><?php _e('Organization', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="organization_id" name="organization_id" style="width: 100%;">
                        <option value=""><?php _e('Select Organization', 'wp-cddu-manager'); ?></option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?php echo esc_attr($org->ID); ?>" <?php selected($organization_id, $org->ID); ?>>
                                <?php echo esc_html($org->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="location"><?php _e('Location', 'wp-cddu-manager'); ?></label></th>
                <td><input type="text" id="location" name="location" value="<?php echo esc_attr($location); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label for="start_date"><?php _e('Start Date', 'wp-cddu-manager'); ?></label></th>
                <td><input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>" /></td>
            </tr>
            <tr>
                <th><label for="end_date"><?php _e('End Date', 'wp-cddu-manager'); ?></label></th>
                <td><input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>" /></td>
            </tr>
            <tr>
                <th><label for="total_hours"><?php _e('Total Hours', 'wp-cddu-manager'); ?></label></th>
                <td><input type="number" id="total_hours" name="total_hours" value="<?php echo esc_attr($total_hours); ?>" step="0.5" min="0" /></td>
            </tr>
            <tr>
                <th><label for="hourly_rate"><?php _e('Hourly Rate (€)', 'wp-cddu-manager'); ?></label></th>
                <td><input type="number" id="hourly_rate" name="hourly_rate" value="<?php echo esc_attr($hourly_rate); ?>" step="0.01" min="0" /></td>
            </tr>
            <tr>
                <th><label for="mission_type"><?php _e('Mission Type', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="mission_type" name="mission_type">
                        <option value="standard" <?php selected($mission_type, 'standard'); ?>><?php _e('Standard', 'wp-cddu-manager'); ?></option>
                        <option value="urgent" <?php selected($mission_type, 'urgent'); ?>><?php _e('Urgent', 'wp-cddu-manager'); ?></option>
                        <option value="long_term" <?php selected($mission_type, 'long_term'); ?>><?php _e('Long Term', 'wp-cddu-manager'); ?></option>
                        <option value="part_time" <?php selected($mission_type, 'part_time'); ?>><?php _e('Part Time', 'wp-cddu-manager'); ?></option>
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
                        <option value="critical" <?php selected($priority, 'critical'); ?>><?php _e('Critical', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="mission_status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="mission_status" name="mission_status">
                        <option value="draft" <?php selected($mission_status, 'draft'); ?>><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                        <option value="open" <?php selected($mission_status, 'open'); ?>><?php _e('Open', 'wp-cddu-manager'); ?></option>
                        <option value="in_progress" <?php selected($mission_status, 'in_progress'); ?>><?php _e('In Progress', 'wp-cddu-manager'); ?></option>
                        <option value="completed" <?php selected($mission_status, 'completed'); ?>><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                        <option value="cancelled" <?php selected($mission_status, 'cancelled'); ?>><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render mission assignment metabox
     */
    public function render_mission_assignment_metabox($post): void {
        $learner_ids = maybe_unserialize(get_post_meta($post->ID, 'learner_ids', true));
        if (!is_array($learner_ids)) {
            $learner_ids = [];
        }
        
        $learners = get_posts([
            'post_type' => 'cddu_learner',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="learner_ids"><?php _e('Assigned Learners', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <select id="learner_ids" name="learner_ids[]" multiple size="5" style="width: 100%;">
                        <?php foreach ($learners as $learner): ?>
                            <option value="<?php echo esc_attr($learner->ID); ?>" <?php selected(in_array($learner->ID, $learner_ids), true); ?>>
                                <?php echo esc_html($learner->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><small><?php _e('Hold Ctrl/Cmd to select multiple learners', 'wp-cddu-manager'); ?></small>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render mission training metabox
     */
    public function render_mission_training_metabox($post): void {
        $training_action = get_post_meta($post->ID, 'training_action', true);
        $training_modalities = maybe_unserialize(get_post_meta($post->ID, 'training_modalities', true));
        if (!is_array($training_modalities)) {
            $training_modalities = [];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="training_action"><?php _e('Training Action', 'wp-cddu-manager'); ?></label></th>
                <td><input type="text" id="training_action" name="training_action" value="<?php echo esc_attr($training_action); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label><?php _e('Training Modalities', 'wp-cddu-manager'); ?></label></th>
                <td>
                    <label><input type="checkbox" name="training_modalities[]" value="in_person" <?php checked(in_array('in_person', $training_modalities), true); ?>> <?php _e('In-Person', 'wp-cddu-manager'); ?></label><br>
                    <label><input type="checkbox" name="training_modalities[]" value="remote" <?php checked(in_array('remote', $training_modalities), true); ?>> <?php _e('Remote', 'wp-cddu-manager'); ?></label><br>
                    <label><input type="checkbox" name="training_modalities[]" value="hybrid" <?php checked(in_array('hybrid', $training_modalities), true); ?>> <?php _e('Hybrid', 'wp-cddu-manager'); ?></label><br>
                    <label><input type="checkbox" name="training_modalities[]" value="elearning" <?php checked(in_array('elearning', $training_modalities), true); ?>> <?php _e('E-Learning', 'wp-cddu-manager'); ?></label><br>
                    <label><input type="checkbox" name="training_modalities[]" value="practical" <?php checked(in_array('practical', $training_modalities), true); ?>> <?php _e('Practical Workshop', 'wp-cddu-manager'); ?></label><br>
                    <label><input type="checkbox" name="training_modalities[]" value="theoretical" <?php checked(in_array('theoretical', $training_modalities), true); ?>> <?php _e('Theoretical Course', 'wp-cddu-manager'); ?></label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save mission meta data
     */
    public function save_mission_meta($post_id, $post): void {
        // Verify nonce
        if (!isset($_POST['cddu_mission_nonce']) || !wp_verify_nonce($_POST['cddu_mission_nonce'], 'cddu_mission_nonce')) {
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
        
        // Handle description update - update post content if mission_description is provided
        if (isset($_POST['mission_description'])) {
            $description = wp_kses_post($_POST['mission_description']);
            
            // Update post content without triggering infinite loop
            remove_action('save_post_cddu_mission', [$this, 'save_mission_meta'], 10);
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $description
            ]);
            add_action('save_post_cddu_mission', [$this, 'save_mission_meta'], 10, 2);
        }
        
        // Save mission meta data from metaboxes
        $meta_fields = [
            'organization_id' => intval($_POST['organization_id'] ?? 0),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
            'total_hours' => floatval($_POST['total_hours'] ?? 0),
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'mission_type' => sanitize_text_field($_POST['mission_type'] ?? 'standard'),
            'priority' => sanitize_text_field($_POST['priority'] ?? 'medium'),
            'mission_status' => sanitize_text_field($_POST['mission_status'] ?? 'draft'),
            'learner_ids' => array_map('intval', $_POST['learner_ids'] ?? []),
            'training_action' => sanitize_text_field($_POST['training_action'] ?? ''),
            'training_modalities' => array_map('sanitize_text_field', $_POST['training_modalities'] ?? []),
        ];
        
        // Update metadata
        foreach ($meta_fields as $meta_key => $meta_value) {
            if (in_array($meta_key, ['learner_ids', 'training_modalities'])) {
                update_post_meta($post_id, $meta_key, maybe_serialize($meta_value));
            } else {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        
        // Update the serialized mission data for compatibility
        $updated_post = get_post($post_id);
        $mission_data = array_merge($meta_fields, [
            'title' => $updated_post->post_title,
            'description' => $updated_post->post_content,
            'required_skills' => [], // Not available in metaboxes, preserve existing
            'status' => $meta_fields['mission_status'],
        ]);
        
        // Preserve existing required_skills if not in metaboxes
        $existing_mission = maybe_unserialize(get_post_meta($post_id, 'mission', true));
        if (is_array($existing_mission) && isset($existing_mission['required_skills'])) {
            $mission_data['required_skills'] = $existing_mission['required_skills'];
        }
        
        update_post_meta($post_id, 'mission', maybe_serialize($mission_data));
        update_post_meta($post_id, 'updated_date', current_time('mysql'));
        
        // Calculate and update mission statistics
        $mission_stats = $this->calculate_mission_stats($mission_data);
        foreach ($mission_stats as $key => $value) {
            update_post_meta($post_id, 'mission_' . $key, $value);
        }
    }
    
    /**
     * Customize mission editor interface
     */
    public function customize_mission_editor(): void {
        global $post_type;
        
        if ($post_type === 'cddu_mission') {
            echo '<style>
                #postdivrich { display: none !important; }
                #normal-sortables .postbox { width: 100% !important; }
                #cddu-mission-edit-interface { width: 100% !important; }
                .cddu-mission-edit-interface { 
                    background: #fff; 
                    padding: 20px; 
                    margin: 10px 0;
                    border: 1px solid #c3c4c7;
                    border-radius: 4px;
                }
                /* Hide custom fields metabox */
                #postcustom { display: none !important; }
                #pagecustomdiv { display: none !important; }
                /* Hide other default metaboxes that might show custom fields */
                #slugdiv { display: none !important; }
                #edit-slug-box { display: none !important; }
            </style>';
        }
    }
    
    /**
     * Add mission edit metabox
     */
    public function add_mission_edit_metabox(): void {
        // Mission Information metabox
        add_meta_box(
            'cddu-mission-information',
            __('Mission Information', 'wp-cddu-manager'),
            [$this, 'render_information_metabox'],
            'cddu_mission',
            'normal',
            'high'
        );
        
        // Schedule & Budget metabox
        add_meta_box(
            'cddu-mission-schedule-budget',
            __('Schedule & Budget', 'wp-cddu-manager'),
            [$this, 'render_schedule_budget_metabox'],
            'cddu_mission',
            'normal',
            'high'
        );
        
        // Learners & Training metabox
        add_meta_box(
            'cddu-mission-learners-training',
            __('Learners & Training', 'wp-cddu-manager'),
            [$this, 'render_learners_training_metabox'],
            'cddu_mission',
            'normal',
            'high'
        );
    }
    
    /**
     * Remove default WordPress metaboxes for missions
     */
    public function remove_default_metaboxes(): void {
        // Remove custom fields metabox
        remove_meta_box('postcustom', 'cddu_mission', 'normal');
        remove_meta_box('postcustom', 'cddu_mission', 'advanced');
        
        // Remove slug metabox
        remove_meta_box('slugdiv', 'cddu_mission', 'normal');
        remove_meta_box('slugdiv', 'cddu_mission', 'advanced');
        
        // Remove excerpt metabox if it exists
        remove_meta_box('postexcerpt', 'cddu_mission', 'normal');
        remove_meta_box('postexcerpt', 'cddu_mission', 'advanced');
        
        // Remove trackbacks metabox
        remove_meta_box('trackbacksdiv', 'cddu_mission', 'normal');
        remove_meta_box('trackbacksdiv', 'cddu_mission', 'advanced');
        
        // Remove comments metabox
        remove_meta_box('commentstatusdiv', 'cddu_mission', 'normal');
        remove_meta_box('commentstatusdiv', 'cddu_mission', 'advanced');
        
        // Remove author metabox
        remove_meta_box('authordiv', 'cddu_mission', 'normal');
        remove_meta_box('authordiv', 'cddu_mission', 'advanced');
    }
    
    /**
     * Render mission information metabox
     */
    public function render_information_metabox($post): void {
        // Add nonce for security
        wp_nonce_field('cddu_mission_nonce', 'cddu_mission_nonce');
        
        // Get existing data
        $existing_mission_data = $this->get_mission_data($post);
        $organizations = $this->get_organizations();
        
        // Include the mission information template
        include CDDU_MNGR_PATH . 'templates/admin/missions/metaboxes/information.php';
    }
    
    /**
     * Render schedule & budget metabox
     */
    public function render_schedule_budget_metabox($post): void {
        // Get existing data
        $existing_mission_data = $this->get_mission_data($post);
        
        // Include the schedule & budget template
        include CDDU_MNGR_PATH . 'templates/admin/missions/metaboxes/schedule-budget.php';
    }
    
    /**
     * Render learners & training metabox
     */
    public function render_learners_training_metabox($post): void {
        // Get existing data
        $existing_mission_data = $this->get_mission_data($post);
        $learners = $this->get_learners();
        
        // Include the learners & training template
        include CDDU_MNGR_PATH . 'templates/admin/missions/metaboxes/learners-training.php';
    }
    
    /**
     * Get mission data for a post
     */
    private function get_mission_data($post): array {
        $mission_id = $post->ID;
        $editing_mode = $mission_id > 0;
        
        // For new missions, initialize empty data structure
        $existing_mission_data = [
            'title' => $post->post_title ?? '',
            'description' => $post->post_content ?? '',
            'organization_id' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'total_hours' => '',
            'hourly_rate' => '',
            'mission_type' => 'standard',
            'priority' => 'medium',
            'mission_status' => 'draft',
            'required_skills' => [],
            'learner_ids' => [],
            'training_action' => '',
            'training_modalities' => [],
        ];
        
        // If editing existing mission, load actual data
        if ($editing_mode) {
            $existing_mission_data = [
                'title' => $post->post_title,
                'description' => $post->post_content,
                'organization_id' => get_post_meta($mission_id, 'organization_id', true),
                'location' => get_post_meta($mission_id, 'location', true),
                'start_date' => get_post_meta($mission_id, 'start_date', true),
                'end_date' => get_post_meta($mission_id, 'end_date', true),
                'total_hours' => get_post_meta($mission_id, 'total_hours', true),
                'hourly_rate' => get_post_meta($mission_id, 'hourly_rate', true),
                'mission_type' => get_post_meta($mission_id, 'mission_type', true) ?: 'standard',
                'priority' => get_post_meta($mission_id, 'priority', true) ?: 'medium',
                'mission_status' => get_post_meta($mission_id, 'mission_status', true) ?: 'draft',
                'required_skills' => maybe_unserialize(get_post_meta($mission_id, 'required_skills', true)) ?: [],
                'learner_ids' => maybe_unserialize(get_post_meta($mission_id, 'learner_ids', true)) ?: [],
                'training_action' => get_post_meta($mission_id, 'training_action', true),
                'training_modalities' => maybe_unserialize(get_post_meta($mission_id, 'training_modalities', true)) ?: [],
            ];
        }
        
        return $existing_mission_data;
    }
    
    /**
     * Get organizations
     */
    private function get_organizations(): array {
        return get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
    }
    
    /**
     * Get learners
     */
    private function get_learners(): array {
        return get_posts([
            'post_type' => 'cddu_learner',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
    }
}
