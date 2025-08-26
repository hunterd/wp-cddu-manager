<?php
namespace CDDU_Manager\Rest;

class InstructorOrganizationController extends \WP_REST_Controller {
    protected $namespace = 'cddu-manager/v1';
    protected $rest_base = 'instructor-organizations';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/assign', [
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'assign_instructor'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => $this->get_assign_args(),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/unassign', [
            [
                'methods' => \WP_REST_Server::DELETABLE,
                'callback' => [$this, 'unassign_instructor'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => $this->get_assign_args(),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<organization_id>\d+)/instructors', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_organization_instructors'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'organization_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<instructor_id>\d+)/organizations', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_instructor_organizations'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'instructor_id' => [
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/search', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'search_instructors'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'search' => [
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'organization_id' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                    'assigned' => [
                        'required' => false,
                        'type' => 'boolean',
                    ],
                ],
            ],
        ]);
    }

    public function check_permission(): bool {
        return current_user_can('cddu_manage_instructors') || current_user_can('manage_options');
    }

    protected function get_assign_args(): array {
        return [
            'organization_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param) && get_post_type($param) === 'cddu_organization';
                },
                'sanitize_callback' => 'absint',
            ],
            'instructor_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    if (!is_numeric($param)) return false;
                    $user = get_userdata($param);
                    return $user && in_array('cddu_instructor', $user->roles);
                },
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    public function assign_instructor(\WP_REST_Request $request): \WP_REST_Response {
        $organization_id = $request->get_param('organization_id');
        $instructor_id = $request->get_param('instructor_id');

        // Validate permissions for this specific assignment
        $permission_errors = \CDDU_Manager\RoleManager::validate_assignment_permission($organization_id, $instructor_id);
        if (!empty($permission_errors)) {
            return new \WP_REST_Response([
                'error' => implode(' ', $permission_errors)
            ], 403);
        }

        // Verify both posts exist and are the correct types
        if (!$this->validate_post_exists($organization_id, 'cddu_organization')) {
            return new \WP_REST_Response([
                'error' => __('Invalid organization ID', 'wp-cddu-manager')
            ], 400);
        }

        $instructor_user = get_userdata($instructor_id);
        if (!$instructor_user || !in_array('cddu_instructor', $instructor_user->roles)) {
            return new \WP_REST_Response([
                'error' => __('Invalid instructor ID', 'wp-cddu-manager')
            ], 400);
        }

        // Check if already assigned
        if ($this->is_instructor_assigned($organization_id, $instructor_id)) {
            return new \WP_REST_Response([
                'error' => __('Instructor is already assigned to this organization', 'wp-cddu-manager')
            ], 409);
        }

        // Assign instructor to organization
        $success = $this->add_instructor_to_organization($organization_id, $instructor_id);

        if ($success) {
            // Log assignment
            $this->log_assignment_action('assign', $organization_id, $instructor_id);

            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Instructor successfully assigned to organization', 'wp-cddu-manager'),
                'data' => [
                    'organization_id' => $organization_id,
                    'instructor_id' => $instructor_id,
                ]
            ], 200);
        }

        return new \WP_REST_Response([
            'error' => __('Failed to assign instructor to organization', 'wp-cddu-manager')
        ], 500);
    }

    public function unassign_instructor(\WP_REST_Request $request): \WP_REST_Response {
        $organization_id = $request->get_param('organization_id');
        $instructor_id = $request->get_param('instructor_id');

        // Validate permissions for this specific assignment
        $permission_errors = \CDDU_Manager\RoleManager::validate_assignment_permission($organization_id, $instructor_id);
        if (!empty($permission_errors)) {
            return new \WP_REST_Response([
                'error' => implode(' ', $permission_errors)
            ], 403);
        }

        // Verify both posts exist and are the correct types
        if (!$this->validate_post_exists($organization_id, 'cddu_organization')) {
            return new \WP_REST_Response([
                'error' => __('Invalid organization ID', 'wp-cddu-manager')
            ], 400);
        }

        $instructor_user = get_userdata($instructor_id);
        if (!$instructor_user || !in_array('cddu_instructor', $instructor_user->roles)) {
            return new \WP_REST_Response([
                'error' => __('Invalid instructor ID', 'wp-cddu-manager')
            ], 400);
        }

        // Check if instructor is assigned
        if (!$this->is_instructor_assigned($organization_id, $instructor_id)) {
            return new \WP_REST_Response([
                'error' => __('Instructor is not assigned to this organization', 'wp-cddu-manager')
            ], 409);
        }

        // Check if instructor has active contracts with this organization
        if ($this->has_active_contracts($organization_id, $instructor_id)) {
            return new \WP_REST_Response([
                'error' => __('Cannot unassign instructor with active contracts. Please complete or cancel contracts first.', 'wp-cddu-manager')
            ], 409);
        }

        // Unassign instructor from organization
        $success = $this->remove_instructor_from_organization($organization_id, $instructor_id);

        if ($success) {
            // Log unassignment
            $this->log_assignment_action('unassign', $organization_id, $instructor_id);

            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Instructor successfully unassigned from organization', 'wp-cddu-manager'),
                'data' => [
                    'organization_id' => $organization_id,
                    'instructor_id' => $instructor_id,
                ]
            ], 200);
        }

        return new \WP_REST_Response([
            'error' => __('Failed to unassign instructor from organization', 'wp-cddu-manager')
        ], 500);
    }

    public function get_organization_instructors(\WP_REST_Request $request): \WP_REST_Response {
        $organization_id = $request->get_param('organization_id');

        if (!$this->validate_post_exists($organization_id, 'cddu_organization')) {
            return new \WP_REST_Response([
                'error' => __('Invalid organization ID', 'wp-cddu-manager')
            ], 400);
        }

        $assigned_instructors = $this->get_assigned_instructors($organization_id);
        $instructor_data = [];

        foreach ($assigned_instructors as $instructor_id) {
            $instructor_user = get_userdata($instructor_id);
            if (!$instructor_user || !in_array('cddu_instructor', $instructor_user->roles)) continue;

            // Get additional instructor meta
            $address = get_user_meta($instructor_id, 'address', true);
            $phone = get_user_meta($instructor_id, 'phone', true);

            $instructor_data[] = [
                'id' => $instructor_id,
                'title' => $instructor_user->display_name,
                'first_name' => $instructor_user->first_name ?? '',
                'last_name' => $instructor_user->last_name ?? '',
                'full_name' => $instructor_user->display_name,
                'email' => $instructor_user->user_email,
                'address' => $address,
                'phone' => $phone,
                'assigned_date' => $this->get_assignment_date($organization_id, $instructor_id),
                'active_contracts' => $this->count_active_contracts($organization_id, $instructor_id),
            ];
        }

        return new \WP_REST_Response([
            'success' => true,
            'data' => $instructor_data,
            'total' => count($instructor_data),
        ], 200);
    }

    public function get_instructor_organizations(\WP_REST_Request $request): \WP_REST_Response {
        $instructor_id = $request->get_param('instructor_id');

        // Validate instructor user instead of post
        $instructor_user = get_userdata($instructor_id);
        if (!$instructor_user || !in_array('cddu_instructor', $instructor_user->roles)) {
            return new \WP_REST_Response([
                'error' => __('Invalid instructor ID', 'wp-cddu-manager')
            ], 400);
        }

        $organizations = $this->get_instructor_assigned_organizations($instructor_id);
        $organization_data = [];

        foreach ($organizations as $org_id) {
            $org_post = get_post($org_id);
            if (!$org_post) continue;

            $org_meta = get_post_meta($org_id, 'org', true);
            $org_info = maybe_unserialize($org_meta) ?: [];

            $organization_data[] = [
                'id' => $org_id,
                'title' => $org_post->post_title,
                'name' => $org_info['name'] ?? $org_post->post_title,
                'address' => $org_info['address'] ?? '',
                'legal_representative' => $org_info['legal_representative'] ?? '',
                'assigned_date' => $this->get_assignment_date($org_id, $instructor_id),
                'active_contracts' => $this->count_active_contracts($org_id, $instructor_id),
            ];
        }

        return new \WP_REST_Response([
            'success' => true,
            'data' => $organization_data,
            'total' => count($organization_data),
        ], 200);
    }

    public function search_instructors(\WP_REST_Request $request): \WP_REST_Response {
        $search = $request->get_param('search') ?? '';
        $organization_id = $request->get_param('organization_id');
        $assigned = $request->get_param('assigned');

        $args = [
            'role' => 'cddu_instructor',
            'number' => -1,
            'fields' => 'all_with_meta'
        ];

        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        $instructors = get_users($args);
        $instructor_data = [];

        foreach ($instructors as $instructor) {
            // Check assignment status if organization_id is provided
            $is_assigned = false;
            if ($organization_id) {
                $is_assigned = $this->is_instructor_assigned($organization_id, $instructor->ID);
            }

            // Filter by assignment status if specified
            if ($assigned !== null && $organization_id) {
                if ($assigned && !$is_assigned) continue;
                if (!$assigned && $is_assigned) continue;
            }

            // Get additional instructor meta
            $address = get_user_meta($instructor->ID, 'address', true);
            $phone = get_user_meta($instructor->ID, 'phone', true);

            $instructor_data[] = [
                'id' => $instructor->ID,
                'title' => $instructor->display_name,
                'first_name' => $instructor->first_name ?? '',
                'last_name' => $instructor->last_name ?? '',
                'full_name' => $instructor->display_name,
                'email' => $instructor->user_email,
                'address' => $address,
                'phone' => $phone,
                'is_assigned' => $is_assigned,
            ];
        }

        return new \WP_REST_Response([
            'success' => true,
            'data' => $instructor_data,
            'total' => count($instructor_data),
            'filters' => [
                'search' => $search,
                'organization_id' => $organization_id,
                'assigned' => $assigned,
            ]
        ], 200);
    }

    // Helper methods

    protected function validate_post_exists(int $post_id, string $post_type): bool {
        $post = get_post($post_id);
        return $post && $post->post_type === $post_type && $post->post_status === 'publish';
    }

    protected function is_instructor_assigned(int $organization_id, int $instructor_id): bool {
        $assigned_instructors = $this->get_assigned_instructors($organization_id);
        return in_array($instructor_id, $assigned_instructors, true);
    }

    protected function get_assigned_instructors(int $organization_id): array {
        $assigned = get_post_meta($organization_id, 'assigned_instructors', true);
        $assigned = maybe_unserialize($assigned) ?: [];
        return array_map('intval', array_filter((array) $assigned));
    }

    protected function add_instructor_to_organization(int $organization_id, int $instructor_id): bool {
        $assigned_instructors = $this->get_assigned_instructors($organization_id);
        
        if (!in_array($instructor_id, $assigned_instructors, true)) {
            $assigned_instructors[] = $instructor_id;
            $result = update_post_meta($organization_id, 'assigned_instructors', maybe_serialize($assigned_instructors));
            
            // Also add reverse relationship for easier queries
            $this->add_organization_to_instructor($instructor_id, $organization_id);
            
            return $result !== false;
        }
        
        return true; // Already assigned
    }

    protected function remove_instructor_from_organization(int $organization_id, int $instructor_id): bool {
        $assigned_instructors = $this->get_assigned_instructors($organization_id);
        $assigned_instructors = array_values(array_filter($assigned_instructors, function($id) use ($instructor_id) {
            return $id !== $instructor_id;
        }));
        
        $result = update_post_meta($organization_id, 'assigned_instructors', maybe_serialize($assigned_instructors));
        
        // Also remove reverse relationship
        $this->remove_organization_from_instructor($instructor_id, $organization_id);
        
        return $result !== false;
    }

    protected function add_organization_to_instructor(int $instructor_id, int $organization_id): void {
        $assigned_organizations = get_post_meta($instructor_id, 'assigned_organizations', true);
        $assigned_organizations = maybe_unserialize($assigned_organizations) ?: [];
        $assigned_organizations = array_map('intval', array_filter((array) $assigned_organizations));
        
        if (!in_array($organization_id, $assigned_organizations, true)) {
            $assigned_organizations[] = $organization_id;
            update_post_meta($instructor_id, 'assigned_organizations', maybe_serialize($assigned_organizations));
        }
    }

    protected function remove_organization_from_instructor(int $instructor_id, int $organization_id): void {
        $assigned_organizations = get_post_meta($instructor_id, 'assigned_organizations', true);
        $assigned_organizations = maybe_unserialize($assigned_organizations) ?: [];
        $assigned_organizations = array_map('intval', array_filter((array) $assigned_organizations));
        
        $assigned_organizations = array_values(array_filter($assigned_organizations, function($id) use ($organization_id) {
            return $id !== $organization_id;
        }));
        
        update_post_meta($instructor_id, 'assigned_organizations', maybe_serialize($assigned_organizations));
    }

    protected function get_instructor_assigned_organizations(int $instructor_id): array {
        $assigned = get_user_meta($instructor_id, 'assigned_organizations', true);
        $assigned = maybe_unserialize($assigned) ?: [];
        return array_map('intval', array_filter((array) $assigned));
    }

    protected function has_active_contracts(int $organization_id, int $instructor_id): bool {
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'organization_id',
                    'value' => $organization_id,
                    'compare' => '='
                ],
                [
                    'key' => 'instructor_user_id',
                    'value' => $instructor_id,
                    'compare' => '='
                ],
                [
                    'key' => 'status',
                    'value' => ['draft', 'active', 'pending_signature'],
                    'compare' => 'IN'
                ]
            ],
            'numberposts' => 1
        ]);
        
        return !empty($contracts);
    }

    protected function count_active_contracts(int $organization_id, int $instructor_id): int {
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'organization_id',
                    'value' => $organization_id,
                    'compare' => '='
                ],
                [
                    'key' => 'instructor_user_id',
                    'value' => $instructor_id,
                    'compare' => '='
                ],
                [
                    'key' => 'status',
                    'value' => ['draft', 'active', 'pending_signature'],
                    'compare' => 'IN'
                ]
            ],
            'numberposts' => -1
        ]);
        
        return count($contracts);
    }

    protected function get_assignment_date(int $organization_id, int $instructor_id): string {
        // Try to get from assignment history meta
        $assignment_history = get_post_meta($organization_id, 'instructor_assignment_history', true);
        $assignment_history = maybe_unserialize($assignment_history) ?: [];
        
        if (isset($assignment_history[$instructor_id]['assigned_date'])) {
            return $assignment_history[$instructor_id]['assigned_date'];
        }
        
        return '';
    }

    protected function log_assignment_action(string $action, int $organization_id, int $instructor_id): void {
        $history = get_post_meta($organization_id, 'instructor_assignment_history', true);
        $history = maybe_unserialize($history) ?: [];
        
        if (!isset($history[$instructor_id])) {
            $history[$instructor_id] = [];
        }
        
        $history[$instructor_id][$action . '_date'] = current_time('mysql');
        $history[$instructor_id][$action . '_by'] = get_current_user_id();
        
        if ($action === 'assign') {
            $history[$instructor_id]['assigned_date'] = current_time('mysql');
        }
        
        update_post_meta($organization_id, 'instructor_assignment_history', maybe_serialize($history));
    }
}
