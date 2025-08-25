<?php
/**
 * Plugin Name: CDDU Manager (Contrats & Avenants)
 * Description: Génération et suivi des CDDU + avenants, déclarations d'heures, PDF, signature électronique (Yousign/DocuSign via providers).
 * Version: 0.1.0
 * Author: David + ChatGPT
 * Requires PHP: 8.0
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) { exit; }

define('CDDU_MNGR_PATH', plugin_dir_path(__FILE__));
define('CDDU_MNGR_URL', plugin_dir_url(__FILE__));
define('CDDU_MNGR_VERSION', '0.1.0');

require_once CDDU_MNGR_PATH . 'includes/Autoloader.php';
CDDU_Manager\Autoloader::init();

register_activation_hook(__FILE__, function() {
    // Create roles & capabilities (minimal)
    $role = get_role('administrator');
    if ($role && !$role->has_cap('cddu_manage')) {
        $role->add_cap('cddu_manage');
    }
    add_role('formateur', 'Formateur', ['read' => true]);
});

add_action('plugins_loaded', function() {
    (new CDDU_Manager\Plugin())->init();
});
