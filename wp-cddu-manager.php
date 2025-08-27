<?php
/**
 * Plugin Name: CDDU Manager (Contracts & Addendums)
 * Description: Generate and track CDDU contracts and addendums, timesheet declarations, PDFs and electronic signatures (Yousign/DocuSign via providers).
 * Version: 0.1.0
 * Author: David Mussard
 * Requires PHP: 8.0
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) { exit; }

define('CDDU_MNGR_FILE', __FILE__);
define('CDDU_MNGR_PATH', plugin_dir_path(__FILE__));
define('CDDU_MNGR_URL', plugin_dir_url(__FILE__));
define('CDDU_MNGR_VERSION', '0.1.0');
define('CDDU_MNGR_TEXTDOMAIN', 'wp-cddu-manager');

require_once CDDU_MNGR_PATH . 'includes/Autoloader.php';
CDDU_Manager\Autoloader::init();

register_activation_hook(__FILE__, function() {
    // Create roles & capabilities (minimal)
    $role = get_role('administrator');
    if ($role && !$role->has_cap('cddu_manage')) {
        $role->add_cap('cddu_manage');
    }
    add_role('cddu_instructor', 'Instructor', ['read' => true]);
    add_role('cddu_organization_manager', 'organization manager', ['read' => true]);
});

add_action('init', function() {
    (new CDDU_Manager\Plugin())->init();
}, 5); // Priority 5 to ensure it runs before other init hooks
