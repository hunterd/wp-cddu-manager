<?php
namespace CDDU_Manager\Admin;

class InstructorManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cddu_assign_instructor', [$this, 'ajax_assign_instructor']);
        add_action('wp_ajax_cddu_unassign_instructor', [$this, 'ajax_unassign_instructor']);
        add_action('wp_ajax_cddu_search_instructors', [$this, 'ajax_search_instructors']);
        add_action('wp_ajax_cddu_get_organization_instructors', [$this, 'ajax_get_organization_instructors']);
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_organization',
            __('Manage Instructors', 'wp-cddu-manager'),
            __('Manage Instructors', 'wp-cddu-manager'),
            'cddu_manage_instructors',
            'manage-instructors',
            [$this, 'render_manage_instructors_page']
        );
    }

    public function enqueue_scripts($hook): void {
        if ($hook !== 'cddu_organization_page_manage-instructors') {
            return;
        }
        
        wp_enqueue_script(
            'cddu-instructor-manager',
            CDDU_MNGR_URL . 'assets/js/instructor-manager.js',
            ['jquery', 'wp-util'],
            CDDU_MNGR_VERSION,
            true
        );
        
        wp_enqueue_style(
            'cddu-instructor-manager',
            CDDU_MNGR_URL . 'assets/css/instructor-manager.css',
            [],
            CDDU_MNGR_VERSION
        );
        
        wp_localize_script('cddu-instructor-manager', 'cddu_instructor_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('cddu-manager/v1/instructor-organizations/'),
            'nonce' => wp_create_nonce('cddu_instructor_nonce'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'strings' => [
                'assign_success' => __('Instructor successfully assigned', 'wp-cddu-manager'),
                'unassign_success' => __('Instructor successfully unassigned', 'wp-cddu-manager'),
                'confirm_unassign' => __('Are you sure you want to unassign this instructor?', 'wp-cddu-manager'),
                'error_occurred' => __('An error occurred. Please try again.', 'wp-cddu-manager'),
                'loading' => __('Loading...', 'wp-cddu-manager'),
                'no_results' => __('No instructors found', 'wp-cddu-manager'),
                'search_placeholder' => __('Search instructors...', 'wp-cddu-manager'),
            ]
        ]);
    }

    public function render_manage_instructors_page(): void {
        // Check user capabilities
        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-cddu-manager'));
        }

        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish',
            'post__in' => \CDDU_Manager\RoleManager::get_user_accessible_organizations() ?: [0], // Show none if no access
        ]);

        $selected_org_id = intval($_GET['organization_id'] ?? 0);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Manage Instructors', 'wp-cddu-manager'); ?></h1>
            <p class="description">
                <?php echo esc_html__('Assign and manage instructors for organizations. Instructors must be assigned to an organization before creating contracts.', 'wp-cddu-manager'); ?>
            </p>

            <div class="cddu-instructor-management">
                <!-- Organization Selection -->
                <div class="cddu-form-section">
                    <h2><?php echo esc_html__('Select Organization', 'wp-cddu-manager'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="organization-select"><?php echo esc_html__('Organization', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <select id="organization-select" class="regular-text">
                                    <option value=""><?php echo esc_html__('-- Select Organization --', 'wp-cddu-manager'); ?></option>
                                    <?php foreach ($organizations as $org): ?>
                                        <option value="<?php echo esc_attr($org->ID); ?>" <?php selected($selected_org_id, $org->ID); ?>>
                                            <?php echo esc_html($org->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="load-organization" class="button button-secondary">
                                    <?php echo esc_html__('Load Organization', 'wp-cddu-manager'); ?>
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Organization Details (Hidden initially) -->
                <div id="organization-details" class="cddu-form-section" style="display: none;">
                    <h2><?php echo esc_html__('Organization Details', 'wp-cddu-manager'); ?></h2>
                    <div id="organization-info">
                        <!-- Will be populated via AJAX -->
                    </div>
                </div>

                <!-- Assigned Instructors -->
                <div id="assigned-instructors-section" class="cddu-form-section" style="display: none;">
                    <h2><?php echo esc_html__('Assigned Instructors', 'wp-cddu-manager'); ?></h2>
                    <div id="assigned-instructors-list">
                        <!-- Will be populated via AJAX -->
                    </div>
                </div>

                <!-- Add New Instructor -->
                <div id="add-instructor-section" class="cddu-form-section" style="display: none;">
                    <h2><?php echo esc_html__('Add Instructor', 'wp-cddu-manager'); ?></h2>
                    
                    <div class="instructor-search-container">
                        <input type="text" id="instructor-search" placeholder="<?php echo esc_attr__('Search instructors...', 'wp-cddu-manager'); ?>" class="regular-text" />
                        <button type="button" id="search-instructors" class="button button-secondary">
                            <?php echo esc_html__('Search', 'wp-cddu-manager'); ?>
                        </button>
                    </div>

                    <div id="instructor-search-results" class="instructor-results">
                        <!-- Will be populated via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div id="loading-indicator" style="display: none;">
                <p><?php echo esc_html__('Loading...', 'wp-cddu-manager'); ?></p>
            </div>

            <!-- Success/Error messages -->
            <div id="message-container"></div>
        </div>

        <style>
        .cddu-instructor-management {
            max-width: 1200px;
        }

        .cddu-form-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }

        .cddu-form-section h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .instructor-search-container {
            margin-bottom: 20px;
        }

        .instructor-search-container input {
            margin-right: 10px;
        }

        .instructor-results {
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
        }

        .instructor-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .instructor-item:last-child {
            border-bottom: none;
        }

        .instructor-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }

        .instructor-info .details {
            color: #666;
            font-size: 12px;
        }

        .instructor-actions {
            flex-shrink: 0;
        }

        .assigned-instructor {
            background-color: #f9f9f9;
        }

        .assigned-instructor .instructor-actions .assign-btn {
            display: none;
        }

        .instructor-item:hover {
            background-color: #f5f5f5;
        }

        #loading-indicator {
            text-align: center;
            padding: 20px;
        }

        .notice {
            margin: 10px 0;
        }

        .instructor-stats {
            margin-top: 5px;
            font-size: 11px;
            color: #999;
        }

        .contracts-count {
            background: #0073aa;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Auto-load organization if provided in URL
            <?php if ($selected_org_id): ?>
            $('#organization-select').val(<?php echo $selected_org_id; ?>);
            $('#load-organization').trigger('click');
            <?php endif; ?>
        });
        </script>
        <?php
    }

    public function ajax_assign_instructor(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');

        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }

        $organization_id = intval($_POST['organization_id'] ?? 0);
        $instructor_id = intval($_POST['instructor_id'] ?? 0);

        if (!$organization_id || !$instructor_id) {
            wp_send_json_error(['message' => __('Invalid organization or instructor ID', 'wp-cddu-manager')]);
        }

        // Use REST controller to handle assignment
        $controller = new \CDDU_Manager\Rest\InstructorOrganizationController();
        $request = new \WP_REST_Request('POST', '/cddu-manager/v1/instructor-organizations/assign');
        $request->set_param('organization_id', $organization_id);
        $request->set_param('instructor_id', $instructor_id);

        $response = $controller->assign_instructor($request);
        $data = $response->get_data();

        if ($response->get_status() === 200) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }
    }

    public function ajax_unassign_instructor(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');

        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }

        $organization_id = intval($_POST['organization_id'] ?? 0);
        $instructor_id = intval($_POST['instructor_id'] ?? 0);

        if (!$organization_id || !$instructor_id) {
            wp_send_json_error(['message' => __('Invalid organization or instructor ID', 'wp-cddu-manager')]);
        }

        // Use REST controller to handle unassignment
        $controller = new \CDDU_Manager\Rest\InstructorOrganizationController();
        $request = new \WP_REST_Request('DELETE', '/cddu-manager/v1/instructor-organizations/unassign');
        $request->set_param('organization_id', $organization_id);
        $request->set_param('instructor_id', $instructor_id);

        $response = $controller->unassign_instructor($request);
        $data = $response->get_data();

        if ($response->get_status() === 200) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }
    }

    public function ajax_search_instructors(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');

        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }

        $search = sanitize_text_field($_POST['search'] ?? '');
        $organization_id = intval($_POST['organization_id'] ?? 0);

        // Use REST controller to handle search
        $controller = new \CDDU_Manager\Rest\InstructorOrganizationController();
        $request = new \WP_REST_Request('GET', '/cddu-manager/v1/instructor-organizations/search');
        $request->set_param('search', $search);
        $request->set_param('organization_id', $organization_id);

        $response = $controller->search_instructors($request);
        $data = $response->get_data();

        if ($response->get_status() === 200) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }
    }

    public function ajax_get_organization_instructors(): void {
        check_ajax_referer('cddu_instructor_nonce', 'nonce');

        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }

        $organization_id = intval($_POST['organization_id'] ?? 0);

        if (!$organization_id) {
            wp_send_json_error(['message' => __('Invalid organization ID', 'wp-cddu-manager')]);
        }

        // Get organization details
        $org_post = get_post($organization_id);
        if (!$org_post || $org_post->post_type !== 'cddu_organization') {
            wp_send_json_error(['message' => __('Organization not found', 'wp-cddu-manager')]);
        }

        $org_meta = get_post_meta($organization_id, 'org', true);
        $org_info = maybe_unserialize($org_meta) ?: [];

        // Use REST controller to get assigned instructors
        $controller = new \CDDU_Manager\Rest\InstructorOrganizationController();
        $request = new \WP_REST_Request('GET', '/cddu-manager/v1/instructor-organizations/' . $organization_id . '/instructors');
        $request->set_param('organization_id', $organization_id);

        $response = $controller->get_organization_instructors($request);
        $instructors_data = $response->get_data();

        if ($response->get_status() === 200) {
            wp_send_json_success([
                'organization' => [
                    'id' => $organization_id,
                    'title' => $org_post->post_title,
                    'name' => $org_info['name'] ?? $org_post->post_title,
                    'address' => $org_info['address'] ?? '',
                    'legal_representative' => $org_info['legal_representative'] ?? '',
                ],
                'instructors' => $instructors_data['data'] ?? [],
                'total_instructors' => $instructors_data['total'] ?? 0,
            ]);
        } else {
            wp_send_json_error($instructors_data);
        }
    }
}
