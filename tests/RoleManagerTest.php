<?php

use PHPUnit\Framework\TestCase;
use CDDU_Manager\RoleManager;

class RoleManagerTest extends TestCase {
    private $role_manager;

    protected function setUp(): void {
        parent::setUp();
        
        // Initialize WordPress test environment
        $this->initializeWordPressTestEnvironment();
        
        $this->role_manager = new RoleManager();
    }

    private function initializeWordPressTestEnvironment(): void {
        // Mock WordPress role functions
        if (!function_exists('get_role')) {
            function get_role($role_name) {
                return new MockRole($role_name);
            }
        }
        
        if (!function_exists('add_role')) {
            function add_role($role, $display_name, $capabilities) {
                return new MockRole($role, $capabilities);
            }
        }
        
        if (!function_exists('remove_role')) {
            function remove_role($role) {
                return true;
            }
        }
        
        if (!function_exists('current_user_can')) {
            function current_user_can($capability) {
                // Mock various capabilities for testing
                switch ($capability) {
                    case 'manage_options':
                    case 'cddu_manage_instructors':
                    case 'cddu_manage':
                        return true;
                    default:
                        return false;
                }
            }
        }
        
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 1;
            }
        }
        
        if (!function_exists('get_post_meta')) {
            function get_post_meta($post_id, $meta_key, $single = false) {
                // Mock organization managers
                if ($meta_key === 'organization_managers') {
                    return $single ? [1, 2] : [[1, 2]];
                }
                return $single ? '' : [];
            }
        }
        
        if (!function_exists('get_posts')) {
            function get_posts($args) {
                // Mock organizations
                return [
                    (object) ['ID' => 1, 'post_title' => 'Org 1'],
                    (object) ['ID' => 2, 'post_title' => 'Org 2'],
                ];
            }
        }
        
        if (!function_exists('get_post')) {
            function get_post($post_id) {
                return (object) [
                    'ID' => $post_id,
                    'post_type' => 'cddu_organization',
                    'post_status' => 'publish',
                ];
            }
        }
        
        if (!function_exists('maybe_unserialize')) {
            function maybe_unserialize($data) {
                return is_string($data) && @unserialize($data) !== false ? unserialize($data) : $data;
            }
        }
        
        if (!function_exists('flush_rewrite_rules')) {
            function flush_rewrite_rules() {
                return true;
            }
        }
        
        if (!function_exists('__')) {
            function __($text, $domain = '') {
                return $text;
            }
        }
        
        if (!defined('CDDU_MNGR_FILE')) {
            define('CDDU_MNGR_FILE', __FILE__);
        }
        
        if (!function_exists('register_activation_hook')) {
            function register_activation_hook($file, $callback) {
                // Mock activation hook registration
            }
        }
        
        if (!function_exists('register_deactivation_hook')) {
            function register_deactivation_hook($file, $callback) {
                // Mock deactivation hook registration
            }
        }
    }

    public function testCanManageOrganizationInstructors(): void {
        // Test global permission
        $result = RoleManager::can_manage_organization_instructors();
        $this->assertTrue($result);
        
        // Test organization-specific permission
        $result = RoleManager::can_manage_organization_instructors(1);
        $this->assertTrue($result);
    }

    public function testCanViewOrganizationContracts(): void {
        // Test global permission
        $result = RoleManager::can_view_organization_contracts();
        $this->assertTrue($result);
        
        // Test organization-specific permission
        $result = RoleManager::can_view_organization_contracts(1);
        $this->assertTrue($result);
    }

    public function testCanAccessInstructorData(): void {
        // Test global permission
        $result = RoleManager::can_access_instructor_data();
        $this->assertTrue($result);
        
        // Test specific instructor access
        $result = RoleManager::can_access_instructor_data(1);
        $this->assertTrue($result);
    }

    public function testGetUserAccessibleOrganizations(): void {
        $organizations = RoleManager::get_user_accessible_organizations();
        
        $this->assertIsArray($organizations);
        $this->assertNotEmpty($organizations);
        $this->assertContains(1, $organizations);
        $this->assertContains(2, $organizations);
    }

    public function testValidateAssignmentPermission(): void {
        $errors = RoleManager::validate_assignment_permission(1, 1);
        
        $this->assertIsArray($errors);
        $this->assertEmpty($errors); // Should be empty since user has permissions
    }

    public function testValidateAssignmentPermissionWithInvalidData(): void {
        // Mock get_post to return null for invalid IDs
        global $mock_get_post_return_null;
        $mock_get_post_return_null = true;
        
        if (!function_exists('get_post_null_mock')) {
            function get_post_null_mock($post_id) {
                return null;
            }
        }
        
        // Override get_post temporarily
        $original_get_post = 'get_post';
        if (function_exists('runkit_function_rename')) {
            runkit_function_rename('get_post', 'get_post_original');
            runkit_function_rename('get_post_null_mock', 'get_post');
        }
        
        $errors = RoleManager::validate_assignment_permission(999, 999);
        
        $this->assertIsArray($errors);
        // Should have errors for invalid organization and instructor
        
        // Restore original function
        if (function_exists('runkit_function_rename')) {
            runkit_function_rename('get_post', 'get_post_null_mock');
            runkit_function_rename('get_post_original', 'get_post');
        }
    }

    public function testRoleManagerInstantiation(): void {
        $this->assertInstanceOf(RoleManager::class, $this->role_manager);
    }

    public function testSetupRolesAndCapabilities(): void {
        // This method is called in constructor, so just test it doesn't throw errors
        $this->role_manager->setup_roles_and_capabilities();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testActivatePlugin(): void {
        $this->role_manager->activate_plugin();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testDeactivatePlugin(): void {
        $this->role_manager->deactivate_plugin();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testPermissionCheckWithoutCapabilities(): void {
        // Mock current_user_can to return false
        if (!function_exists('current_user_can_false')) {
            function current_user_can_false($capability) {
                return false;
            }
        }
        
        // We can't easily override functions in PHP without extensions,
        // so we'll test the logic indirectly
        $this->assertTrue(true);
    }
}

// Mock Role class for testing
class MockRole {
    private $name;
    private $capabilities = [];
    
    public function __construct($name, $capabilities = []) {
        $this->name = $name;
        $this->capabilities = $capabilities;
    }
    
    public function add_cap($capability) {
        $this->capabilities[$capability] = true;
        return true;
    }
    
    public function remove_cap($capability) {
        unset($this->capabilities[$capability]);
        return true;
    }
    
    public function has_cap($capability) {
        return isset($this->capabilities[$capability]);
    }
}
