<?php
/**
 * Template for Mission Information metabox
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cddu-metabox-content">
    <table class="form-table">
        <tr>
            <th><label for="organization_id"><?php _e('Organization', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <select id="organization_id" name="organization_id" required style="width: 100%;">
                    <option value=""><?php _e('Select an organization', 'wp-cddu-manager'); ?></option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo esc_attr($org->ID); ?>" <?php selected(isset($existing_mission_data['organization_id']) ? $existing_mission_data['organization_id'] : '', $org->ID); ?>>
                            <?php echo esc_html($org->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="mission_type"><?php _e('Mission Type', 'wp-cddu-manager'); ?></label></th>
            <td>
                <select id="mission_type" name="mission_type">
                    <option value="standard" <?php selected(isset($existing_mission_data['mission_type']) ? $existing_mission_data['mission_type'] : 'standard', 'standard'); ?>><?php _e('Standard', 'wp-cddu-manager'); ?></option>
                    <option value="urgent" <?php selected(isset($existing_mission_data['mission_type']) ? $existing_mission_data['mission_type'] : 'standard', 'urgent'); ?>><?php _e('Urgent', 'wp-cddu-manager'); ?></option>
                    <option value="long_term" <?php selected(isset($existing_mission_data['mission_type']) ? $existing_mission_data['mission_type'] : 'standard', 'long_term'); ?>><?php _e('Long Term', 'wp-cddu-manager'); ?></option>
                    <option value="part_time" <?php selected(isset($existing_mission_data['mission_type']) ? $existing_mission_data['mission_type'] : 'standard', 'part_time'); ?>><?php _e('Part Time', 'wp-cddu-manager'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="priority"><?php _e('Priority', 'wp-cddu-manager'); ?></label></th>
            <td>
                <select id="priority" name="priority">
                    <option value="low" <?php selected(isset($existing_mission_data['priority']) ? $existing_mission_data['priority'] : 'medium', 'low'); ?>><?php _e('Low', 'wp-cddu-manager'); ?></option>
                    <option value="medium" <?php selected(isset($existing_mission_data['priority']) ? $existing_mission_data['priority'] : 'medium', 'medium'); ?>><?php _e('Medium', 'wp-cddu-manager'); ?></option>
                    <option value="high" <?php selected(isset($existing_mission_data['priority']) ? $existing_mission_data['priority'] : 'medium', 'high'); ?>><?php _e('High', 'wp-cddu-manager'); ?></option>
                    <option value="critical" <?php selected(isset($existing_mission_data['priority']) ? $existing_mission_data['priority'] : 'medium', 'critical'); ?>><?php _e('Critical', 'wp-cddu-manager'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="mission_description"><?php _e('Mission Description', 'wp-cddu-manager'); ?></label></th>
            <td>
                <textarea id="mission_description" name="mission_description" rows="4" style="width: 100%;"><?php echo esc_textarea(isset($existing_mission_data['description']) ? $existing_mission_data['description'] : ''); ?></textarea>
                <br><small><?php _e('This description will be saved as the post content', 'wp-cddu-manager'); ?></small>
            </td>
        </tr>
        <tr>
            <th><label for="location"><?php _e('Location', 'wp-cddu-manager'); ?></label></th>
            <td>
                <input type="text" id="location" name="location" value="<?php echo esc_attr(isset($existing_mission_data['location']) ? $existing_mission_data['location'] : ''); ?>" style="width: 100%;">
            </td>
        </tr>
        <tr>
            <th><label for="mission_status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
            <td>
                <select id="mission_status" name="mission_status">
                    <option value="draft" <?php selected(isset($existing_mission_data['mission_status']) ? $existing_mission_data['mission_status'] : 'draft', 'draft'); ?>><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                    <option value="open" <?php selected(isset($existing_mission_data['mission_status']) ? $existing_mission_data['mission_status'] : 'draft', 'open'); ?>><?php _e('Open', 'wp-cddu-manager'); ?></option>
                    <option value="in_progress" <?php selected(isset($existing_mission_data['mission_status']) ? $existing_mission_data['mission_status'] : 'draft', 'in_progress'); ?>><?php _e('In Progress', 'wp-cddu-manager'); ?></option>
                    <option value="completed" <?php selected(isset($existing_mission_data['mission_status']) ? $existing_mission_data['mission_status'] : 'draft', 'completed'); ?>><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                    <option value="cancelled" <?php selected(isset($existing_mission_data['mission_status']) ? $existing_mission_data['mission_status'] : 'draft', 'cancelled'); ?>><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                </select>
            </td>
        </tr>
    </table>
</div>
