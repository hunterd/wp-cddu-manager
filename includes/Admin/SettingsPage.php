<?php
namespace CDDU_Manager\Admin;

class SettingsPage {
    public function hooks(): void {
        add_action('admin_menu', function() {
            add_options_page('CDDU Manager', 'CDDU Manager', 'manage_options', 'cddu-manager', [$this, 'render']);
        });
        add_action('admin_init', function() {
            register_setting('cddu_manager', 'cddu_signature_provider');
            register_setting('cddu_manager', 'cddu_yousign_api_key');
            register_setting('cddu_manager', 'cddu_yousign_base_url');
            register_setting('cddu_manager', 'cddu_docusign_integrator_key');
            register_setting('cddu_manager', 'cddu_docusign_base_url');
        });
    }
    public function render(): void { ?>
        <div class="wrap">
            <h1>CDDU Manager – Réglages</h1>
            <form method="post" action="options.php">
                <?php settings_fields('cddu_manager'); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row">Provider de signature</th>
                        <td>
                            <select name="cddu_signature_provider">
                                <?php $p = get_option('cddu_signature_provider','yousign'); ?>
                                <option value="yousign" <?php selected($p,'yousign'); ?>>Yousign</option>
                                <option value="docusign" <?php selected($p,'docusign'); ?>>DocuSign</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th scope="row">Yousign Base URL</th>
                        <td><input type="text" name="cddu_yousign_base_url" value="<?php echo esc_attr(get_option('cddu_yousign_base_url','https://api.yousign.com')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row">Yousign API Key</th>
                        <td><input type="password" name="cddu_yousign_api_key" value="<?php echo esc_attr(get_option('cddu_yousign_api_key','')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row">DocuSign Base URL</th>
                        <td><input type="text" name="cddu_docusign_base_url" value="<?php echo esc_attr(get_option('cddu_docusign_base_url','')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row">DocuSign Integrator Key</th>
                        <td><input type="password" name="cddu_docusign_integrator_key" value="<?php echo esc_attr(get_option('cddu_docusign_integrator_key','')); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php }
}
