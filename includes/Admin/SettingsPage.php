<?php
namespace CDDU_Manager\Admin;

class SettingsPage {
    public function hooks(): void {
        add_action('admin_menu', function() {
            add_options_page('CDDU Manager', 'CDDU Manager', 'manage_options', 'cddu-manager', [$this, 'render']);
            add_submenu_page('options-general.php', 'Preview CDDU', 'Preview CDDU', 'manage_options', 'cddu-manager-preview', [$this, 'render_preview']);
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
            <h1><?php echo esc_html__('CDDU Manager – Settings', 'wp-cddu-manager'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('cddu_manager'); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><?php echo esc_html__('Signature provider', 'wp-cddu-manager'); ?></th>
                        <td>
                            <select name="cddu_signature_provider">
                                <?php $p = get_option('cddu_signature_provider','yousign'); ?>
                                <option value="yousign" <?php selected($p,'yousign'); ?>><?php echo esc_html__('Yousign', 'wp-cddu-manager'); ?></option>
                                <option value="docusign" <?php selected($p,'docusign'); ?>><?php echo esc_html__('DocuSign', 'wp-cddu-manager'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr><th scope="row"><?php echo esc_html__('Yousign Base URL', 'wp-cddu-manager'); ?></th>
                        <td><input type="text" name="cddu_yousign_base_url" value="<?php echo esc_attr(get_option('cddu_yousign_base_url','https://api.yousign.com')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row"><?php echo esc_html__('Yousign API Key', 'wp-cddu-manager'); ?></th>
                        <td><input type="password" name="cddu_yousign_api_key" value="<?php echo esc_attr(get_option('cddu_yousign_api_key','')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row"><?php echo esc_html__('DocuSign Base URL', 'wp-cddu-manager'); ?></th>
                        <td><input type="text" name="cddu_docusign_base_url" value="<?php echo esc_attr(get_option('cddu_docusign_base_url','')); ?>" class="regular-text"></td>
                    </tr>
                    <tr><th scope="row"><?php echo esc_html__('DocuSign Integrator Key', 'wp-cddu-manager'); ?></th>
                        <td><input type="password" name="cddu_docusign_integrator_key" value="<?php echo esc_attr(get_option('cddu_docusign_integrator_key','')); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button(esc_html__('Save Changes', 'wp-cddu-manager')); ?>
            </form>
        </div>
    <?php }

    public function render_preview(): void
    {
        // Find latest draft contract
        $args = [
            'post_type' => 'cddu_contract',
            'post_status' => ['publish','draft'],
            'numberposts' => 1,
        ];
        $posts = get_posts($args);
        if (empty($posts)) {
            echo '<div class="wrap"><h1>' . esc_html__('Preview CDDU', 'wp-cddu-manager') . '</h1><p>' . esc_html__('No contracts found. Create a contract first.', 'wp-cddu-manager') . '</p></div>';
            return;
        }
        $post = $posts[0];
        $meta = get_post_meta($post->ID);
        $org = maybe_unserialize($meta['org'][0] ?? '{}');
        $formateur = maybe_unserialize($meta['formateur'][0] ?? '{}');
        $mission = maybe_unserialize($meta['mission'][0] ?? '{}');
        // Use English keys with French fallbacks
        $calc = \CDDU_Manager\Calculations::calculate([
            'annual_hours' => $mission['annual_hours'] ?? $mission['H_a'] ?? 0,
            'hourly_rate' => $mission['hourly_rate'] ?? $mission['taux_horaire'] ?? 0,
            'start_date' => $mission['start_date'] ?? $mission['date_debut'] ?? '1970-01-01',
            'end_date' => $mission['end_date'] ?? $mission['date_fin'] ?? '1970-01-01',
        ]);

        $template = CDDU_MNGR_PATH . 'templates/contracts/cddu.html.php';
        $html = \CDDU_Manager\DocumentGenerator::renderTemplate($template, ['org' => $org, 'formateur' => $formateur, 'mission' => $mission, 'calc' => $calc]);

        echo '<div class="wrap"><h1>' . esc_html__('Preview CDDU', 'wp-cddu-manager') . '</h1>';
        echo '<div style="border:1px solid #ddd;padding:10px;background:#fff">';
        echo $html;
        echo '</div></div>';
    }
}
