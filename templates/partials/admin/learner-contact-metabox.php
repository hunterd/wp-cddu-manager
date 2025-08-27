<?php
/**
 * Learner contact metabox template
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
        <th scope="row"><label for="learner_email"><?php _e('Email', 'wp-cddu-manager'); ?></label></th>
        <td><input type="email" id="learner_email" name="learner[email]" value="<?php echo esc_attr($learner['email'] ?? ''); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_phone"><?php _e('Phone', 'wp-cddu-manager'); ?></label></th>
        <td><input type="tel" id="learner_phone" name="learner[phone]" value="<?php echo esc_attr($learner['phone'] ?? ''); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_emergency_contact"><?php _e('Emergency Contact', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="learner_emergency_contact" name="learner[emergency_contact]" value="<?php echo esc_attr($learner['emergency_contact'] ?? ''); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="learner_emergency_phone"><?php _e('Emergency Phone', 'wp-cddu-manager'); ?></label></th>
        <td><input type="tel" id="learner_emergency_phone" name="learner[emergency_phone]" value="<?php echo esc_attr($learner['emergency_phone'] ?? ''); ?>" class="regular-text" /></td>
    </tr>
</table>
