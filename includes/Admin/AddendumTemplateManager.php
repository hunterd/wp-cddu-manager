<?php
namespace CDDU_Manager\Admin;

class AddendumTemplateManager {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_cddu_save_addendum_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_cddu_load_addendum_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_cddu_delete_addendum_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_cddu_get_addendum_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_cddu_get_default_addendum_templates', [$this, 'ajax_get_default_templates']);
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_addendum',
            __('Addendum Templates', 'wp-cddu-manager'),
            __('Addendum Templates', 'wp-cddu-manager'),
            'cddu_manage',
            'addendum-templates',
            [$this, 'render_templates_page']
        );
    }

    public function render_templates_page(): void {
        $templates = $this->get_saved_templates();
        $default_templates = $this->get_default_templates();
        include CDDU_MNGR_PATH . 'templates/admin/addendum-templates.php';
    }

    private function get_saved_templates(): array {
        $templates = get_option('cddu_addendum_templates', []);
        return is_array($templates) ? $templates : [];
    }

    /**
     * Get list of default addendum templates from the templates/addendums directory
     */
    public function get_default_templates(): array {
        $templates_dir = CDDU_MNGR_PATH . 'templates/addendums/';
        $templates = [];
        
        if (is_dir($templates_dir)) {
            $files = glob($templates_dir . '*.html');
            foreach ($files as $file) {
                $filename = basename($file, '.html');
                $display_name = $this->format_template_name($filename);
                $templates[$filename] = [
                    'name' => $display_name,
                    'path' => $file,
                    'type' => 'default'
                ];
            }
        }
        
        return $templates;
    }

    /**
     * Format template filename for display
     */
    private function format_template_name(string $filename): string {
        $name = str_replace(['addendum-', '-'], ['', ' '], $filename);
        return ucwords($name);
    }

    /**
     * Load content from a default template file
     */
    public function get_default_template_content(string $template_name): string {
        $template_path = CDDU_MNGR_PATH . 'templates/addendums/' . $template_name . '.html';
        
        if (!file_exists($template_path)) {
            return '';
        }
        
        return file_get_contents($template_path);
    }

    public function ajax_save_template(): void {
        check_ajax_referer('cddu_addendum_nonce', 'nonce');
        
        if (!current_user_can('cddu_manage')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }
        
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        $template_content = wp_kses_post($_POST['template_content'] ?? '');
        
        if (empty($template_name) || empty($template_content)) {
            wp_send_json_error(['message' => __('Template name and content are required', 'wp-cddu-manager')]);
        }
        
        $templates = $this->get_saved_templates();
        $templates[$template_name] = [
            'content' => $template_content,
            'created_date' => current_time('mysql'),
            'created_by' => get_current_user_id(),
            'type' => 'custom'
        ];
        
        update_option('cddu_addendum_templates', $templates);
        
        wp_send_json_success([
            'message' => sprintf(__('Addendum template "%s" saved successfully', 'wp-cddu-manager'), $template_name),
            'templates' => array_keys($templates)
        ]);
    }

    public function ajax_load_template(): void {
        check_ajax_referer('cddu_addendum_nonce', 'nonce');
        
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        $template_type = sanitize_text_field($_POST['template_type'] ?? 'custom');
        
        if (empty($template_name)) {
            wp_send_json_error(['message' => __('Template name is required', 'wp-cddu-manager')]);
        }
        
        if ($template_type === 'default') {
            $content = $this->get_default_template_content($template_name);
            if (empty($content)) {
                wp_send_json_error(['message' => __('Default template not found', 'wp-cddu-manager')]);
            }
            
            wp_send_json_success([
                'content' => $content,
                'template_data' => [
                    'type' => 'default',
                    'name' => $template_name
                ]
            ]);
        } else {
            $templates = $this->get_saved_templates();
            if (!isset($templates[$template_name])) {
                wp_send_json_error(['message' => __('Template not found', 'wp-cddu-manager')]);
            }
            
            wp_send_json_success([
                'content' => $templates[$template_name]['content'],
                'template_data' => $templates[$template_name]
            ]);
        }
    }

    public function ajax_delete_template(): void {
        check_ajax_referer('cddu_addendum_nonce', 'nonce');
        
        if (!current_user_can('cddu_manage')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-cddu-manager')]);
        }
        
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        if (empty($template_name)) {
            wp_send_json_error(['message' => __('Template name is required', 'wp-cddu-manager')]);
        }
        
        $templates = $this->get_saved_templates();
        if (!isset($templates[$template_name])) {
            wp_send_json_error(['message' => __('Template not found', 'wp-cddu-manager')]);
        }
        
        unset($templates[$template_name]);
        update_option('cddu_addendum_templates', $templates);
        
        wp_send_json_success([
            'message' => sprintf(__('Addendum template "%s" deleted successfully', 'wp-cddu-manager'), $template_name),
            'templates' => array_keys($templates)
        ]);
    }

    public function ajax_get_templates(): void {
        check_ajax_referer('cddu_addendum_nonce', 'nonce');
        
        $templates = $this->get_saved_templates();
        wp_send_json_success([
            'templates' => $templates
        ]);
    }

    public function ajax_get_default_templates(): void {
        check_ajax_referer('cddu_addendum_nonce', 'nonce');
        
        $templates = $this->get_default_templates();
        wp_send_json_success([
            'templates' => $templates
        ]);
    }
}
