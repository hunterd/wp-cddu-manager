<?php
namespace CDDU_Manager\Admin;

class OrganizationManager {
    public function __construct() {
        // No admin menu for organizations - using standard WordPress post type listing
    }

    /**
     * Register organization post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_organization', [
            'label' => __('Organizations', 'wp-cddu-manager'),
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-building',
            'supports' => ['title'],
            'capability_type' => 'post',
        ]);
    }

    /**
     * Add organization metaboxes
     */
    public function add_metaboxes(): void {
        add_meta_box('cddu_organization_details', __('Organization Details', 'wp-cddu-manager'), [$this, 'render_organization_metabox'], 'cddu_organization', 'normal', 'high');
        add_meta_box('cddu_organization_managers', __('Manage Managers', 'wp-cddu-manager'), [$this, 'render_organization_managers_metabox'], 'cddu_organization', 'side', 'default');
        add_meta_box('cddu_organization_instructors', __('Manage Instructors', 'wp-cddu-manager'), [$this, 'render_organization_instructors_metabox'], 'cddu_organization', 'normal', 'default');
    }

    /**
     * Render organization metabox
     */
    public function render_organization_metabox(\WP_Post $post): void {
        // Read 'org' meta - it can be serialized array or JSON string
        $org = [];
        $org_raw = get_post_meta($post->ID, 'org', true);
        
        if (!empty($org_raw)) {
            // Try to unserialize first (WordPress serialization)
            $maybe_unserialized = maybe_unserialize($org_raw);
            if (is_array($maybe_unserialized)) {
                $org = $maybe_unserialized;
            } else {
                // Try JSON decode
                $maybe_json = json_decode($org_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($maybe_json)) {
                    $org = $maybe_json;
                } else {
                    // If it's a string, try to decode it as JSON again (double encoded?)
                    $maybe_json2 = json_decode($maybe_unserialized, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($maybe_json2)) {
                        $org = $maybe_json2;
                    }
                }
            }
        }
        
        include CDDU_MNGR_PATH . 'templates/partials/admin/organization-metabox.php';
    }

    /**
     * Render organization managers metabox
     */
    public function render_organization_managers_metabox(\WP_Post $post): void {
        // Check user permissions
        if (!current_user_can('cddu_manage') && !current_user_can('manage_options')) {
            echo '<p>' . esc_html__('You do not have permission to manage organization managers.', 'wp-cddu-manager') . '</p>';
            return;
        }

        // Get currently assigned managers
        $assigned_managers = get_post_meta($post->ID, 'organization_managers', true);
        if (!is_array($assigned_managers)) {
            $assigned_managers = maybe_unserialize($assigned_managers) ?: [];
        }
        $assigned_managers = array_map('intval', array_filter((array) $assigned_managers));

        // Get all users with organization manager role
        $all_managers = get_users([
            'role' => 'cddu_organization_manager',
            'orderby' => 'display_name',
            'order' => 'ASC',
            'fields' => 'all_with_meta'
        ]);

        // Get manager statistics
        $manager_stats = [];
        foreach ($all_managers as $manager) {
            // Count organizations managed by this manager
            $managed_orgs = get_posts([
                'post_type' => 'cddu_organization',
                'meta_query' => [
                    [
                        'key' => 'organization_managers',
                        'value' => serialize(strval($manager->ID)),
                        'compare' => 'LIKE'
                    ]
                ],
                'numberposts' => -1,
                'fields' => 'ids'
            ]);

            $manager_stats[$manager->ID] = [
                'managed_organizations' => count($managed_orgs),
                'email' => $manager->user_email,
                'phone' => get_user_meta($manager->ID, 'phone', true),
                'address' => get_user_meta($manager->ID, 'address', true),
                'is_assigned' => in_array($manager->ID, $assigned_managers)
            ];
        }

        wp_nonce_field('cddu_organization_managers_nonce', 'cddu_organization_managers_nonce');
        
        include CDDU_MNGR_PATH . 'templates/partials/admin/organization-managers-metabox.php';
    }

    /**
     * Render organization instructors metabox
     */
    public function render_organization_instructors_metabox(\WP_Post $post): void {
        // Check user permissions
        if (!current_user_can('cddu_manage_instructors') && !current_user_can('manage_options')) {
            echo '<p>' . esc_html__('You do not have permission to manage instructors.', 'wp-cddu-manager') . '</p>';
            return;
        }

        // Get currently assigned instructors (now user IDs instead of post IDs)
        $assigned_instructors = get_post_meta($post->ID, 'assigned_instructors', true);
        if (!is_array($assigned_instructors)) {
            $assigned_instructors = [];
        }

        // Get all users with instructor role
        $all_instructors = get_users([
            'role' => 'cddu_instructor',
            'orderby' => 'display_name',
            'order' => 'ASC',
            'fields' => 'all_with_meta'
        ]);

        // Get instructor statistics
        $instructor_stats = [];
        foreach ($all_instructors as $instructor) {
            // Count active contracts for this instructor
            $active_contracts = get_posts([
                'post_type' => 'cddu_contract',
                'meta_query' => [
                    [
                        'key' => 'instructor_user_id',
                        'value' => $instructor->ID,
                        'compare' => '='
                    ],
                    [
                        'key' => 'status',
                        'value' => ['active', 'signed'],
                        'compare' => 'IN'
                    ]
                ],
                'numberposts' => -1,
                'fields' => 'ids'
            ]);

            $instructor_stats[$instructor->ID] = [
                'active_contracts' => count($active_contracts),
                'email' => $instructor->user_email,
                'phone' => get_user_meta($instructor->ID, 'phone', true),
                'address' => get_user_meta($instructor->ID, 'address', true),
                'is_assigned' => in_array($instructor->ID, $assigned_instructors)
            ];
        }

        wp_nonce_field('cddu_organization_instructors_nonce', 'cddu_organization_instructors_nonce');
        
        include CDDU_MNGR_PATH . 'templates/partials/admin/organization-instructors-metabox.php';
    }

    /**
     * Save organization meta
     */
    public function save_organization_meta(int $post_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
        if (!current_user_can('edit_post', $post_id)) { return; }

        $org = $_POST['org'] ?? null;
        if ($org !== null) {
            update_post_meta($post_id, 'org', maybe_serialize($org));
        }
        
        // Save organization managers if nonce is valid and user has permission
        if (isset($_POST['cddu_organization_managers_nonce']) && 
            wp_verify_nonce($_POST['cddu_organization_managers_nonce'], 'cddu_organization_managers_nonce') &&
            (current_user_can('cddu_manage') || current_user_can('manage_options'))) {
            
            if (array_key_exists('organization_managers', $_POST)) {
                $managers = $_POST['organization_managers'];
                // sanitize ints
                $managers = array_map('intval', (array) $managers);
                update_post_meta($post_id, 'organization_managers', maybe_serialize($managers));
            } else {
                // no managers selected -> remove meta
                delete_post_meta($post_id, 'organization_managers');
            }
        }

        // Save assigned instructors
        if (isset($_POST['cddu_organization_instructors_nonce']) && 
            wp_verify_nonce($_POST['cddu_organization_instructors_nonce'], 'cddu_organization_instructors_nonce') &&
            current_user_can('cddu_manage_instructors')) {
            
            if (array_key_exists('assigned_instructors', $_POST)) {
                $assigned_instructors = $_POST['assigned_instructors'];
                // Sanitize and validate instructor IDs (now user IDs)
                $assigned_instructors = array_map('intval', (array) $assigned_instructors);
                $assigned_instructors = array_filter($assigned_instructors, function($id) {
                    $user = get_userdata($id);
                    return $user && in_array('cddu_instructor', $user->roles);
                });
                
                // Get previously assigned instructors
                $previous_instructors = get_post_meta($post_id, 'assigned_instructors', true);
                if (!is_array($previous_instructors)) {
                    $previous_instructors = [];
                }
                
                // Validate unassignment - prevent removing instructors with active contracts
                $unassigned_instructors = array_diff($previous_instructors, $assigned_instructors);
                $validation_errors = [];
                
                foreach ($unassigned_instructors as $instructor_id) {
                    $active_contracts = get_posts([
                        'post_type' => 'cddu_contract',
                        'meta_query' => [
                            [
                                'key' => 'instructor_user_id',
                                'value' => $instructor_id,
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
                        $user = get_userdata($instructor_id);
                        $instructor_name = $user ? $user->display_name : __('Unknown instructor', 'wp-cddu-manager');
                        $validation_errors[] = sprintf(
                            __('Cannot unassign instructor "%s" - they have %d active contract(s). Please complete or cancel their contracts first.', 'wp-cddu-manager'),
                            $instructor_name,
                            count($active_contracts)
                        );
                        // Keep the instructor assigned
                        $assigned_instructors[] = $instructor_id;
                    }
                }
                
                // Display validation errors to user
                if (!empty($validation_errors)) {
                    add_action('admin_notices', function() use ($validation_errors) {
                        foreach ($validation_errors as $error) {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
                        }
                    });
                    // Remove duplicates that might have been added back
                    $assigned_instructors = array_unique($assigned_instructors);
                }
                
                // Update organization meta
                update_post_meta($post_id, 'assigned_instructors', $assigned_instructors);
                
                // Update instructor user meta to reflect organization assignment
                // Remove organization from previously assigned instructors (only those without active contracts)
                foreach ($previous_instructors as $instructor_id) {
                    if (!in_array($instructor_id, $assigned_instructors)) {
                        $instructor_orgs = get_user_meta($instructor_id, 'assigned_organizations', true);
                        if (!is_array($instructor_orgs)) {
                            $instructor_orgs = [];
                        }
                        $instructor_orgs = array_diff($instructor_orgs, [$post_id]);
                        update_user_meta($instructor_id, 'assigned_organizations', $instructor_orgs);
                    }
                }
                
                // Add organization to newly assigned instructors
                foreach ($assigned_instructors as $instructor_id) {
                    $instructor_orgs = get_user_meta($instructor_id, 'assigned_organizations', true);
                    if (!is_array($instructor_orgs)) {
                        $instructor_orgs = [];
                    }
                    if (!in_array($post_id, $instructor_orgs)) {
                        $instructor_orgs[] = $post_id;
                        update_user_meta($instructor_id, 'assigned_organizations', $instructor_orgs);
                    }
                }
            } else {
                // No instructors selected -> validate before removing all assignments
                $previous_instructors = get_post_meta($post_id, 'assigned_instructors', true);
                $validation_errors = [];
                $protected_instructors = [];
                
                if (is_array($previous_instructors)) {
                    foreach ($previous_instructors as $instructor_id) {
                        $active_contracts = get_posts([
                            'post_type' => 'cddu_contract',
                            'meta_query' => [
                                [
                                    'key' => 'instructor_user_id',
                                    'value' => $instructor_id,
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
                            $user = get_userdata($instructor_id);
                            $instructor_name = $user ? $user->display_name : __('Unknown instructor', 'wp-cddu-manager');
                            $validation_errors[] = sprintf(
                                __('Cannot unassign instructor "%s" - they have active contracts. Some instructors remain assigned.', 'wp-cddu-manager'),
                                $instructor_name
                            );
                            $protected_instructors[] = $instructor_id;
                        } else {
                            // Safe to remove this instructor
                            $instructor_orgs = get_user_meta($instructor_id, 'assigned_organizations', true);
                            if (is_array($instructor_orgs)) {
                                $instructor_orgs = array_diff($instructor_orgs, [$post_id]);
                                update_user_meta($instructor_id, 'assigned_organizations', $instructor_orgs);
                            }
                        }
                    }
                }
                
                // Display validation errors
                if (!empty($validation_errors)) {
                    add_action('admin_notices', function() use ($validation_errors) {
                        foreach ($validation_errors as $error) {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
                        }
                    });
                    // Keep protected instructors assigned
                    update_post_meta($post_id, 'assigned_instructors', $protected_instructors);
                } else {
                    // Safe to remove all assignments
                    delete_post_meta($post_id, 'assigned_instructors');
                }
            }
        }
    }
}
