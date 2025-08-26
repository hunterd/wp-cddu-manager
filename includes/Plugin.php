<?php
namespace CDDU_Manager;

use CDDU_Manager\Admin\SettingsPage;
use CDDU_Manager\Admin\ContractManager;
use CDDU_Manager\Admin\InstructorManager;
use CDDU_Manager\Admin\ContractTemplateManager;
use CDDU_Manager\Admin\AddendumTemplateManager;
use CDDU_Manager\Frontend\InstructorDashboard;
use CDDU_Manager\Rest\TimesheetsController;
use CDDU_Manager\Rest\InstructorOrganizationController;
use CDDU_Manager\Signature\YousignProvider;
use CDDU_Manager\Signature\DocusignProvider;

class Plugin {
    public function init(): void {
        // Initialize role management first
        new RoleManager();
        
        // Initialize PostTypes with hooks
        $post_types = new PostTypes();
        
        add_action('init', function() use ($post_types) {
            $post_types->register();
            flush_rewrite_rules(); // Only needed during development
        });

        (new SettingsPage())->hooks();
        new ContractManager();
        new ContractTemplateManager();
        new AddendumTemplateManager();
        new InstructorManager();
        new InstructorDashboard();
        new TimesheetProcessor();
        new SignatureManager();
        new DocumentArchive();
        new NotificationManager();

        add_action('rest_api_init', function() {
            (new TimesheetsController())->register_routes();
            (new InstructorOrganizationController())->register_routes();
        });
        
        // Add AJAX handler for mission data
        add_action('wp_ajax_cddu_get_mission_data', [$this, 'ajax_get_mission_data']);
    }

    /** Factory pour le provider de signature */
    public static function signature_provider() {
        $provider = get_option('cddu_signature_provider', 'yousign');
        if ($provider === 'docusign') {
            return new DocusignProvider();
        }
        return new YousignProvider();
    }
    
    public function ajax_get_mission_data(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $mission_id = intval($_POST['mission_id'] ?? 0);
        if (!$mission_id) {
            wp_send_json_error(['message' => __('Invalid mission ID', 'wp-cddu-manager')]);
        }
        
        $mission_meta = get_post_meta($mission_id, 'mission', true);
        $mission_data = maybe_unserialize($mission_meta);
        
        if (!is_array($mission_data)) {
            $mission_data = [];
        }
        
        wp_send_json_success($mission_data);
    }
}
