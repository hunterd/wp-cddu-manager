<?php
namespace CDDU_Manager;

class RoleManager {
    
    public function __construct() {
        add_action('init', [$this, 'setup_roles_and_capabilities']);
        register_activation_hook(CDDU_MNGR_FILE, [$this, 'activate_plugin']);
        register_deactivation_hook(CDDU_MNGR_FILE, [$this, 'deactivate_plugin']);
    }

    public function setup_roles_and_capabilities(): void {
        // Create custom capabilities
        $this->add_cddu_capabilities();
        
        // Setup custom roles
        $this->setup_custom_roles();
    }

    private function add_cddu_capabilities(): void {
        $capabilities = [
            'cddu_manage' => __('Manage CDDU system', 'wp-cddu-manager'),
            'cddu_manage_instructors' => __('Manage instructor assignments', 'wp-cddu-manager'),
            'cddu_view_contracts' => __('View contracts', 'wp-cddu-manager'),
            'cddu_create_contracts' => __('Create contracts', 'wp-cddu-manager'),
            'cddu_edit_contracts' => __('Edit contracts', 'wp-cddu-manager'),
            'cddu_delete_contracts' => __('Delete contracts', 'wp-cddu-manager'),
            'cddu_manage_organizations' => __('Manage organizations', 'wp-cddu-manager'),
            'cddu_manage_signatures' => __('Manage electronic signatures', 'wp-cddu-manager'),
        ];

        // Add capabilities to administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach (array_keys($capabilities) as $cap) {
                $admin_role->add_cap($cap);
            }
        }

        // Add capabilities to editor (for organization managers)
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_capabilities = [
                'cddu_manage',
                'cddu_manage_instructors',
                'cddu_view_contracts',
                'cddu_create_contracts',
                'cddu_edit_contracts',
                'cddu_manage_organizations',
            ];
            
            foreach ($editor_capabilities as $cap) {
                $editor_role->add_cap($cap);
            }
        }
    }

    private function setup_custom_roles(): void {
        // CDDU Organization Manager role
        if (!get_role('cddu_organization_manager')) {
            add_role('cddu_organization_manager', __('CDDU Organization Manager', 'wp-cddu-manager'), [
                'read' => true,
                'cddu_manage' => true,
                'cddu_manage_instructors' => true,
                'cddu_view_contracts' => true,
                'cddu_create_contracts' => true,
                'cddu_edit_contracts' => true,
                'cddu_manage_organizations' => true,
                'edit_posts' => true,
                'upload_files' => true,
            ]);
        }

        // CDDU Instructor role
        if (!get_role('cddu_instructor')) {
            add_role('cddu_instructor', __('CDDU Instructor', 'wp-cddu-manager'), [
                'read' => true,
                'cddu_view_contracts' => true,
            ]);
        }

        // CDDU Administrator role (super admin for CDDU system)
        if (!get_role('cddu_administrator')) {
            add_role('cddu_administrator', __('CDDU Administrator', 'wp-cddu-manager'), [
                'read' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'publish_posts' => true,
                'manage_categories' => true,
                'upload_files' => true,
                'cddu_manage' => true,
                'cddu_manage_instructors' => true,
                'cddu_view_contracts' => true,
                'cddu_create_contracts' => true,
                'cddu_edit_contracts' => true,
                'cddu_delete_contracts' => true,
                'cddu_manage_organizations' => true,
                'cddu_manage_signatures' => true,
            ]);
        }
    }

    public function activate_plugin(): void {
        // Setup roles and capabilities on plugin activation
        $this->setup_roles_and_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate_plugin(): void {
        // Remove custom capabilities from default roles
        $roles_to_clean = ['administrator', 'editor'];
        $capabilities_to_remove = [
            'cddu_manage',
            'cddu_manage_instructors',
            'cddu_view_contracts',
            'cddu_create_contracts',
            'cddu_edit_contracts',
            'cddu_delete_contracts',
            'cddu_manage_organizations',
            'cddu_manage_signatures',
        ];

        foreach ($roles_to_clean as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities_to_remove as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }

        // Optionally remove custom roles (commented out to preserve user assignments)
        // remove_role('cddu_organization_manager');
        // remove_role('cddu_instructor');
        // remove_role('cddu_administrator');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Check if current user can manage instructor assignments for a specific organization
     */
    public static function can_manage_organization_instructors(int $organization_id = 0): bool {
        // Global capability check
        if (current_user_can('cddu_manage_instructors') || current_user_can('manage_options')) {
            return true;
        }

        // Organization-specific check for organization managers
        if ($organization_id > 0 && current_user_can('cddu_manage')) {
            $assigned_managers = get_post_meta($organization_id, 'organization_managers', true);
            $assigned_managers = maybe_unserialize($assigned_managers) ?: [];
            $assigned_managers = array_map('intval', (array) $assigned_managers);
            
            return in_array(get_current_user_id(), $assigned_managers, true);
        }

        return false;
    }

    /**
     * Check if current user can view contracts for a specific organization
     */
    public static function can_view_organization_contracts(int $organization_id = 0): bool {
        // Global capability check
        if (current_user_can('cddu_view_contracts') || current_user_can('manage_options')) {
            return true;
        }

        // Organization-specific check
        if ($organization_id > 0) {
            $assigned_managers = get_post_meta($organization_id, 'organization_managers', true);
            $assigned_managers = maybe_unserialize($assigned_managers) ?: [];
            $assigned_managers = array_map('intval', (array) $assigned_managers);
            
            return in_array(get_current_user_id(), $assigned_managers, true);
        }

        return false;
    }

    /**
     * Check if current user can access instructor data
     */
    public static function can_access_instructor_data(int $instructor_user_id = 0): bool {
        $current_user_id = get_current_user_id();
        
        // Global capability check
        if (current_user_can('cddu_manage') || current_user_can('manage_options')) {
            return true;
        }

        // Instructor can access their own data
        if ($instructor_user_id > 0 && $instructor_user_id === $current_user_id) {
            return true;
        }

        // Check if user is assigned to instructor's organizations
        if ($instructor_user_id > 0) {
            $instructor_organizations = get_user_meta($instructor_user_id, 'assigned_organizations', true);
            $instructor_organizations = maybe_unserialize($instructor_organizations) ?: [];
            
            foreach ($instructor_organizations as $org_id) {
                if (self::can_view_organization_contracts($org_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get user's accessible organizations
     */
    public static function get_user_accessible_organizations(): array {
        if (current_user_can('manage_options') || current_user_can('cddu_manage_organizations')) {
            // Full access - return all organizations
            $organizations = get_posts([
                'post_type' => 'cddu_organization',
                'numberposts' => -1,
                'post_status' => 'publish'
            ]);
            return array_column($organizations, 'ID');
        }

        $accessible_orgs = [];
        $current_user_id = get_current_user_id();

        // Get organizations where user is assigned as manager
        $all_organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        foreach ($all_organizations as $org) {
            $assigned_managers = get_post_meta($org->ID, 'organization_managers', true);
            $assigned_managers = maybe_unserialize($assigned_managers) ?: [];
            $assigned_managers = array_map('intval', (array) $assigned_managers);
            
            if (in_array($current_user_id, $assigned_managers, true)) {
                $accessible_orgs[] = $org->ID;
            }
        }

        return $accessible_orgs;
    }

    /**
     * Validate instructor assignment permissions
     */
    public static function validate_assignment_permission(int $organization_id, int $instructor_id): array {
        $errors = [];

        // Check organization access
        if (!self::can_manage_organization_instructors($organization_id)) {
            $errors[] = __('You do not have permission to manage instructors for this organization.', 'wp-cddu-manager');
        }

        // Check if organization exists
        $org_post = get_post($organization_id);
        if (!$org_post || $org_post->post_type !== 'cddu_organization' || $org_post->post_status !== 'publish') {
            $errors[] = __('Invalid or inactive organization.', 'wp-cddu-manager');
        }

        // Check if instructor exists (now checking user instead of post)
        $instructor_user = get_userdata($instructor_id);
        if (!$instructor_user || !in_array('cddu_instructor', $instructor_user->roles)) {
            $errors[] = __('Invalid or inactive instructor.', 'wp-cddu-manager');
        }

        return $errors;
    }
}
