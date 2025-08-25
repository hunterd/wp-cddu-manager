<?php
namespace CDDU_Manager;

use CDDU_Manager\Admin\SettingsPage;
use CDDU_Manager\Rest\TimesheetsController;
use CDDU_Manager\Signature\YousignProvider;
use CDDU_Manager\Signature\DocusignProvider;

class Plugin {
    public function init(): void {
        add_action('init', function() {
            (new PostTypes())->register();
        });

        (new SettingsPage())->hooks();

        add_action('rest_api_init', function() {
            (new TimesheetsController())->register_routes();
        });
    }

    /** Factory pour le provider de signature */
    public static function signature_provider() {
        $provider = get_option('cddu_signature_provider', 'yousign');
        if ($provider === 'docusign') {
            return new DocusignProvider();
        }
        return new YousignProvider();
    }
}
