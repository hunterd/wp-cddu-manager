<?php
namespace CDDU_Manager\Admin;

class ContractTemplateManager {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_cddu_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_cddu_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_cddu_delete_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_cddu_get_templates', [$this, 'ajax_get_templates']);
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_contract',
            __('Contract Templates', 'wp-cddu-manager'),
            __('Contract Templates', 'wp-cddu-manager'),
            'cddu_manage',
            'contract-templates',
            [$this, 'render_templates_page']
        );
    }

    public function render_templates_page(): void {
        $templates = $this->get_saved_templates();
        include CDDU_MNGR_PATH . 'templates/admin/contract-templates.php';
    }

    private function get_saved_templates(): array {
        $templates = get_option('cddu_contract_templates', []);
        return is_array($templates) ? $templates : [];
    }

    public function ajax_save_template(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
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
            'created_by' => get_current_user_id()
        ];
        
        update_option('cddu_contract_templates', $templates);
        
        wp_send_json_success([
            'message' => sprintf(__('Template "%s" saved successfully', 'wp-cddu-manager'), $template_name),
            'templates' => array_keys($templates)
        ]);
    }

    public function ajax_load_template(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        if (empty($template_name)) {
            wp_send_json_error(['message' => __('Template name is required', 'wp-cddu-manager')]);
        }
        
        $templates = $this->get_saved_templates();
        if (!isset($templates[$template_name])) {
            wp_send_json_error(['message' => __('Template not found', 'wp-cddu-manager')]);
        }
        
        wp_send_json_success([
            'content' => $templates[$template_name]['content'],
            'template_data' => $templates[$template_name]
        ]);
    }

    public function ajax_delete_template(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
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
        update_option('cddu_contract_templates', $templates);
        
        wp_send_json_success([
            'message' => sprintf(__('Template "%s" deleted successfully', 'wp-cddu-manager'), $template_name),
            'templates' => array_keys($templates)
        ]);
    }

    public function ajax_get_templates(): void {
        check_ajax_referer('cddu_contract_nonce', 'nonce');
        
        $templates = $this->get_saved_templates();
        wp_send_json_success([
            'templates' => $templates
        ]);
    }
}
