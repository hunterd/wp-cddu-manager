<?php
/**
 * Template for learner contact information metabox
 *
 * @var array $existing_learner_data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="form-table">
    <tr>
        <th><label for="email"><?php _e('Email Address', 'wp-cddu-manager'); ?></label></th>
        <td><input type="email" id="email" name="email" value="<?php echo esc_attr($existing_learner_data['email']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="phone"><?php _e('Phone Number', 'wp-cddu-manager'); ?></label></th>
        <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($existing_learner_data['phone']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="mobile_phone"><?php _e('Mobile Phone', 'wp-cddu-manager'); ?></label></th>
        <td><input type="tel" id="mobile_phone" name="mobile_phone" value="<?php echo esc_attr($existing_learner_data['mobile_phone']); ?>" style="width: 100%;" /></td>
    </tr>
</table>

<h4><?php _e('Emergency Contact', 'wp-cddu-manager'); ?></h4>
<table class="form-table">
    <tr>
        <th><label for="emergency_contact"><?php _e('Emergency Contact Name', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="emergency_contact" name="emergency_contact" value="<?php echo esc_attr($existing_learner_data['emergency_contact']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="emergency_phone"><?php _e('Emergency Contact Phone', 'wp-cddu-manager'); ?></label></th>
        <td><input type="tel" id="emergency_phone" name="emergency_phone" value="<?php echo esc_attr($existing_learner_data['emergency_phone']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="emergency_relationship"><?php _e('Relationship', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="emergency_relationship" name="emergency_relationship">
                <option value=""><?php _e('Select Relationship', 'wp-cddu-manager'); ?></option>
                <option value="parent" <?php selected($existing_learner_data['emergency_relationship'], 'parent'); ?>><?php _e('Parent', 'wp-cddu-manager'); ?></option>
                <option value="spouse" <?php selected($existing_learner_data['emergency_relationship'], 'spouse'); ?>><?php _e('Spouse/Partner', 'wp-cddu-manager'); ?></option>
                <option value="sibling" <?php selected($existing_learner_data['emergency_relationship'], 'sibling'); ?>><?php _e('Sibling', 'wp-cddu-manager'); ?></option>
                <option value="friend" <?php selected($existing_learner_data['emergency_relationship'], 'friend'); ?>><?php _e('Friend', 'wp-cddu-manager'); ?></option>
                <option value="other" <?php selected($existing_learner_data['emergency_relationship'], 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
</table>
