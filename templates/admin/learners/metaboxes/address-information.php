<?php
/**
 * Template for learner address information metabox
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
        <th><label for="address"><?php _e('Address', 'wp-cddu-manager'); ?></label></th>
        <td><textarea id="address" name="address" rows="3" style="width: 100%;"><?php echo esc_textarea($existing_learner_data['address']); ?></textarea></td>
    </tr>
    <tr>
        <th><label for="city"><?php _e('City', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="city" name="city" value="<?php echo esc_attr($existing_learner_data['city']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="postal_code"><?php _e('Postal Code', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr($existing_learner_data['postal_code']); ?>" /></td>
    </tr>
    <tr>
        <th><label for="country"><?php _e('Country', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="country" name="country" style="width: 100%;">
                <option value=""><?php _e('Select Country', 'wp-cddu-manager'); ?></option>
                <option value="france" <?php selected($existing_learner_data['country'], 'france'); ?>><?php _e('France', 'wp-cddu-manager'); ?></option>
                <option value="belgium" <?php selected($existing_learner_data['country'], 'belgium'); ?>><?php _e('Belgium', 'wp-cddu-manager'); ?></option>
                <option value="switzerland" <?php selected($existing_learner_data['country'], 'switzerland'); ?>><?php _e('Switzerland', 'wp-cddu-manager'); ?></option>
                <option value="canada" <?php selected($existing_learner_data['country'], 'canada'); ?>><?php _e('Canada', 'wp-cddu-manager'); ?></option>
                <option value="other" <?php selected($existing_learner_data['country'], 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
</table>
