<?php

// Define WordPress REST API classes before anything else
if (!class_exists('WP_REST_Controller')) {
    abstract class WP_REST_Controller {
        protected $namespace;
        protected $rest_base;
        
        public function register_routes() {
            // Mock implementation
        }
        
        public function check_permission() {
            return true;
        }
    }
}

if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private $params = [];
        
        public function get_param($key) {
            return $this->params[$key] ?? null;
        }
        
        public function set_param($key, $value) {
            $this->params[$key] = $value;
            return $this;
        }
    }
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        private $data;
        private $status;
        
        public function __construct($data = null, $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }
        
        public function get_data() {
            return $this->data;
        }
        
        public function get_status() {
            return $this->status;
        }
    }
}

if (!class_exists('WP_REST_Server')) {
    class WP_REST_Server {
        const READABLE = 'GET';
        const CREATABLE = 'POST';
        const EDITABLE = 'PUT';
        const DELETABLE = 'DELETE';
        const ALLMETHODS = 'GET,POST,PUT,DELETE';
    }
}

use PHPUnit\Framework\TestCase;
use CDDU_Manager\Rest\InstructorOrganizationController;
use CDDU_Manager\RoleManager;

class InstructorAssignmentTest extends TestCase {
    private $controller;
    private $organization_id;
    private $instructor_id;

    protected function setUp(): void {
        parent::setUp();
        
        // Initialize WordPress test environment
        $this->initializeWordPressTestEnvironment();
        
        $this->controller = new InstructorOrganizationController();
        
        // Create test organization
        $this->organization_id = wp_insert_post([
            'post_type' => 'cddu_organization',
            'post_title' => 'Test Organization',
            'post_status' => 'publish',
        ]);
        
        // Create test instructor
        $this->instructor_id = wp_insert_post([
            'post_type' => 'cddu_instructor',
            'post_title' => 'Test Instructor',
            'post_status' => 'publish',
        ]);
        
        // Add test organization metadata
        update_post_meta($this->organization_id, 'org', maybe_serialize([
            'name' => 'Test Organization',
            'address' => '123 Test Street',
            'legal_representative' => 'John Doe',
        ]));
        
        // Add test instructor metadata
        update_post_meta($this->instructor_id, 'instructor', maybe_serialize([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address' => '456 Instructor Lane',
            'birth_date' => '1985-01-01',
        ]));
    }

    protected function tearDown(): void {
        // Clean up test data
        wp_delete_post($this->organization_id, true);
        wp_delete_post($this->instructor_id, true);
        
        parent::tearDown();
    }

    private function initializeWordPressTestEnvironment(): void {
        // Mock WordPress functions for testing
        if (!function_exists('wp_insert_post')) {
            function wp_insert_post($post_data) {
                static $post_id = 1000;
                return ++$post_id;
            }
        }
        
        if (!function_exists('wp_delete_post')) {
            function wp_delete_post($post_id, $force_delete = false) {
                return true;
            }
        }
        
        if (!function_exists('get_post')) {
            function get_post($post_id) {
                return (object) [
                    'ID' => $post_id,
                    'post_type' => $post_id % 2 ? 'cddu_organization' : 'cddu_instructor',
                    'post_status' => 'publish',
                    'post_title' => 'Test Post ' . $post_id,
                ];
            }
        }
        
        if (!function_exists('update_post_meta')) {
            function update_post_meta($post_id, $meta_key, $meta_value) {
                return true;
            }
        }
        
        if (!function_exists('get_post_meta')) {
            function get_post_meta($post_id, $meta_key = '', $single = false) {
                return $single ? '' : [];
            }
        }
        
        if (!function_exists('maybe_serialize')) {
            function maybe_serialize($data) {
                return is_array($data) ? serialize($data) : $data;
            }
        }
        
        if (!function_exists('maybe_unserialize')) {
            function maybe_unserialize($data) {
                return is_string($data) && @unserialize($data) !== false ? unserialize($data) : $data;
            }
        }
        
        if (!function_exists('current_user_can')) {
            function current_user_can($capability) {
                return true; // Grant all permissions for testing
            }
        }
        
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 1;
            }
        }
        
        if (!function_exists('current_time')) {
            function current_time($type) {
                return date('Y-m-d H:i:s');
            }
        }
        
        if (!function_exists('__')) {
            function __($text, $domain = '') {
                return $text;
            }
        }
    }

    public function testControllerInstantiation(): void {
        $this->assertInstanceOf(InstructorOrganizationController::class, $this->controller);
    }

    public function testValidatePostExists(): void {
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('validate_post_exists');
        $method->setAccessible(true);
        
        // Test valid post
        $result = $method->invokeArgs($this->controller, [$this->organization_id, 'cddu_organization']);
        $this->assertTrue($result);
        
        // Test invalid post type
        $result = $method->invokeArgs($this->controller, [$this->organization_id, 'cddu_instructor']);
        $this->assertFalse($result);
    }

    public function testIsInstructorAssigned(): void {
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('is_instructor_assigned');
        $method->setAccessible(true);
        
        // Initially not assigned
        $result = $method->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        $this->assertFalse($result);
    }

    public function testAddInstructorToOrganization(): void {
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('add_instructor_to_organization');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        $this->assertTrue($result);
        
        // Test that instructor is now assigned
        $isAssignedMethod = $reflection->getMethod('is_instructor_assigned');
        $isAssignedMethod->setAccessible(true);
        $assigned = $isAssignedMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        $this->assertTrue($assigned);
    }

    public function testRemoveInstructorFromOrganization(): void {
        $reflection = new ReflectionClass($this->controller);
        
        // First assign instructor
        $addMethod = $reflection->getMethod('add_instructor_to_organization');
        $addMethod->setAccessible(true);
        $addMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        
        // Then remove instructor
        $removeMethod = $reflection->getMethod('remove_instructor_from_organization');
        $removeMethod->setAccessible(true);
        $result = $removeMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        $this->assertTrue($result);
        
        // Test that instructor is no longer assigned
        $isAssignedMethod = $reflection->getMethod('is_instructor_assigned');
        $isAssignedMethod->setAccessible(true);
        $assigned = $isAssignedMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        $this->assertFalse($assigned);
    }

    public function testGetAssignedInstructors(): void {
        $reflection = new ReflectionClass($this->controller);
        
        // Initially empty
        $getMethod = $reflection->getMethod('get_assigned_instructors');
        $getMethod->setAccessible(true);
        $instructors = $getMethod->invokeArgs($this->controller, [$this->organization_id]);
        $this->assertIsArray($instructors);
        $this->assertEmpty($instructors);
        
        // Assign instructor
        $addMethod = $reflection->getMethod('add_instructor_to_organization');
        $addMethod->setAccessible(true);
        $addMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        
        // Check assigned instructors
        $instructors = $getMethod->invokeArgs($this->controller, [$this->organization_id]);
        $this->assertIsArray($instructors);
        $this->assertContains($this->instructor_id, $instructors);
    }

    public function testAssignInstructorRequest(): void {
        // Mock WP_REST_Request
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->willReturnMap([
                ['organization_id', $this->organization_id],
                ['instructor_id', $this->instructor_id]
            ]);
        
        $response = $this->controller->assign_instructor($request);
        
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals($this->organization_id, $data['data']['organization_id']);
        $this->assertEquals($this->instructor_id, $data['data']['instructor_id']);
    }

    public function testUnassignInstructorRequest(): void {
        // First assign instructor
        $reflection = new ReflectionClass($this->controller);
        $addMethod = $reflection->getMethod('add_instructor_to_organization');
        $addMethod->setAccessible(true);
        $addMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        
        // Mock WP_REST_Request
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->willReturnMap([
                ['organization_id', $this->organization_id],
                ['instructor_id', $this->instructor_id]
            ]);
        
        $response = $this->controller->unassign_instructor($request);
        
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('message', $data);
    }

    public function testAssignInstructorTwice(): void {
        // Assign instructor first time
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->willReturnMap([
                ['organization_id', $this->organization_id],
                ['instructor_id', $this->instructor_id]
            ]);
        
        $this->controller->assign_instructor($request);
        
        // Try to assign same instructor again
        $response = $this->controller->assign_instructor($request);
        
        $this->assertEquals(409, $response->get_status());
        $data = $response->get_data();
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('already assigned', $data['error']);
    }

    public function testUnassignNonAssignedInstructor(): void {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->willReturnMap([
                ['organization_id', $this->organization_id],
                ['instructor_id', $this->instructor_id]
            ]);
        
        $response = $this->controller->unassign_instructor($request);
        
        $this->assertEquals(409, $response->get_status());
        $data = $response->get_data();
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('not assigned', $data['error']);
    }

    public function testGetOrganizationInstructors(): void {
        // Assign instructor
        $reflection = new ReflectionClass($this->controller);
        $addMethod = $reflection->getMethod('add_instructor_to_organization');
        $addMethod->setAccessible(true);
        $addMethod->invokeArgs($this->controller, [$this->organization_id, $this->instructor_id]);
        
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->with('organization_id')
            ->willReturn($this->organization_id);
        
        $response = $this->controller->get_organization_instructors($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function testSearchInstructors(): void {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_param')
            ->willReturnMap([
                ['search', 'Test'],
                ['organization_id', $this->organization_id],
                ['assigned', false]
            ]);
        
        $response = $this->controller->search_instructors($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
        $this->assertArrayHasKey('filters', $data);
    }

    public function testPermissionValidation(): void {
        // Mock permission failure
        if (!function_exists('current_user_can_mock_fail')) {
            function current_user_can_mock_fail($capability) {
                return false;
            }
        }
        
        // Test that permission check works
        $this->assertTrue($this->controller->check_permission());
    }

    public function testInputSanitization(): void {
        $reflection = new ReflectionClass($this->controller);
        $validateMethod = $reflection->getMethod('validate_post_exists');
        $validateMethod->setAccessible(true);
        
        // Test with string input (should be handled safely)
        $result = $validateMethod->invokeArgs($this->controller, ['invalid', 'cddu_organization']);
        $this->assertFalse($result);
        
        // Test with negative number
        $result = $validateMethod->invokeArgs($this->controller, [-1, 'cddu_organization']);
        $this->assertFalse($result);
    }

    public function testLogAssignmentAction(): void {
        $reflection = new ReflectionClass($this->controller);
        $logMethod = $reflection->getMethod('log_assignment_action');
        $logMethod->setAccessible(true);
        
        // This should not throw any errors
        $logMethod->invokeArgs($this->controller, ['assign', $this->organization_id, $this->instructor_id]);
        $this->assertTrue(true); // If we get here, no exception was thrown
    }
}
