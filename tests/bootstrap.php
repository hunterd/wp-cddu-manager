<?php
/**
 * PHPUnit bootstrap file for CDDU Manager Plugin tests
 */

// Set up WordPress test environment constants
if (!defined('WP_TESTS_DIR')) {
    define('WP_TESTS_DIR', '/tmp/wordpress-tests-lib');
}

// Define plugin constants
define('CDDU_MNGR_PATH', dirname(__DIR__) . '/');
define('CDDU_MNGR_URL', 'http://example.com/wp-content/plugins/wp-cddu-manager/');
define('CDDU_MNGR_VERSION', '1.0.0');
define('CDDU_MNGR_FILE', dirname(__DIR__) . '/wp-cddu-manager.php');

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load plugin autoloader
require_once dirname(__DIR__) . '/includes/Autoloader.php';

// Initialize plugin autoloader
\CDDU_Manager\Autoloader::register();

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

// Mock WordPress functions that are commonly used in tests
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'path' => '/tmp/uploads',
            'url' => 'http://example.com/wp-content/uploads',
            'subdir' => '',
            'basedir' => '/tmp/uploads',
            'baseurl' => 'http://example.com/wp-content/uploads',
            'error' => false,
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target) {
        return @mkdir($target, 0755, true);
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit($string) {
        return rtrim($string, '/\\');
    }
}

// Mock WordPress database functions
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        // Mock common options
        switch ($option) {
            case 'cddu_signature_provider':
                return 'yousign';
            case 'home':
            case 'siteurl':
                return 'http://example.com';
            default:
                return $default;
        }
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return true;
    }
}

// Mock WordPress sanitization functions
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('sanitize_url')) {
    function sanitize_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

// Mock WordPress formatting functions
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

// Mock WordPress localization functions
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return esc_html(__($text, $domain));
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return esc_attr(__($text, $domain));
    }
}

// Mock WordPress error handling
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $errors = [];
        private $error_data = [];
        
        public function __construct($code = '', $message = '', $data = '') {
            if (!empty($code)) {
                $this->add($code, $message, $data);
            }
        }
        
        public function add($code, $message, $data = '') {
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
        
        public function get_error_code() {
            $codes = array_keys($this->errors);
            return empty($codes) ? '' : $codes[0];
        }
        
        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return isset($this->errors[$code]) ? $this->errors[$code][0] : '';
        }
        
        public function has_errors() {
            return !empty($this->errors);
        }
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

// Mock WordPress REST API classes
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
        
        public function set_data($data) {
            $this->data = $data;
        }
        
        public function set_status($status) {
            $this->status = $status;
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

if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = array()) {
        return true;
    }
}

if (!function_exists('rest_url')) {
    function rest_url($path = '', $scheme = 'rest') {
        return 'http://example.com/wp-json/' . ltrim($path, '/');
    }
}

// Set up global test state
global $wp_test_mode;
$wp_test_mode = true;

// Initialize test data directory
$test_data_dir = dirname(__FILE__) . '/data';
if (!file_exists($test_data_dir)) {
    @mkdir($test_data_dir, 0755, true);
}

// Set timezone for consistent test results
date_default_timezone_set('UTC');

echo "CDDU Manager Plugin test environment initialized.\n";
