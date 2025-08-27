<?php
/**
 * Learner details metabox template
 * 
 * @var WP_Post $post Current post object
 * @var array $learner Learner data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="form-table">
    <tr>
        <th scope="row"><label for="learner_first_name"><?php _e('First Name', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="learner_first_name" name="learner[first_name]" value="<?php echo esc_attr($learner['first_name'] ?? ''); ?>" class="regular-text" required /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_last_name"><?php _e('Last Name', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="learner_last_name" name="learner[last_name]" value="<?php echo esc_attr($learner['last_name'] ?? ''); ?>" class="regular-text" required /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_birth_date"><?php _e('Birth Date', 'wp-cddu-manager'); ?></label></th>
        <td><input type="date" id="learner_birth_date" name="learner[birth_date]" value="<?php echo esc_attr($learner['birth_date'] ?? ''); ?>" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_social_security"><?php _e('Social Security Number', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="learner_social_security" name="learner[social_security]" value="<?php echo esc_attr($learner['social_security'] ?? ''); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_address"><?php _e('Address', 'wp-cddu-manager'); ?></label></th>
        <td><textarea id="learner_address" name="learner[address]" rows="3" class="large-text"><?php echo esc_textarea($learner['address'] ?? ''); ?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_level"><?php _e('Education Level', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="learner_level" name="learner[level]">
                <option value=""><?php _e('Select level', 'wp-cddu-manager'); ?></option>
                <option value="bac_minus" <?php selected($learner['level'] ?? '', 'bac_minus'); ?>><?php _e('Below Baccalaureate', 'wp-cddu-manager'); ?></option>
                <option value="bac" <?php selected($learner['level'] ?? '', 'bac'); ?>><?php _e('Baccalaureate', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_2" <?php selected($learner['level'] ?? '', 'bac_plus_2'); ?>><?php _e('Bac +2', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_3" <?php selected($learner['level'] ?? '', 'bac_plus_3'); ?>><?php _e('Bac +3', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_5" <?php selected($learner['level'] ?? '', 'bac_plus_5'); ?>><?php _e('Bac +5', 'wp-cddu-manager'); ?></option>
                <option value="other" <?php selected($learner['level'] ?? '', 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="learner_status" name="learner[status]">
                <option value="active" <?php selected($learner['status'] ?? 'active', 'active'); ?>><?php _e('Active', 'wp-cddu-manager'); ?></option>
                <option value="inactive" <?php selected($learner['status'] ?? '', 'inactive'); ?>><?php _e('Inactive', 'wp-cddu-manager'); ?></option>
                <option value="completed" <?php selected($learner['status'] ?? '', 'completed'); ?>><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                <option value="dropped" <?php selected($learner['status'] ?? '', 'dropped'); ?>><?php _e('Dropped', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
</table>
