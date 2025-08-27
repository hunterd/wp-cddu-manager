<?php
/**
 * Template for learner personal information metabox
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
        <th><label for="first_name"><?php _e('First Name', 'wp-cddu-manager'); ?> *</label></th>
        <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($existing_learner_data['first_name']); ?>" style="width: 100%;" required /></td>
    </tr>
    <tr>
        <th><label for="last_name"><?php _e('Last Name', 'wp-cddu-manager'); ?> *</label></th>
        <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($existing_learner_data['last_name']); ?>" style="width: 100%;" required /></td>
    </tr>
    <tr>
        <th><label for="birth_date"><?php _e('Birth Date', 'wp-cddu-manager'); ?></label></th>
        <td><input type="date" id="birth_date" name="birth_date" value="<?php echo esc_attr($existing_learner_data['birth_date']); ?>" /></td>
    </tr>
    <tr>
        <th><label for="birth_place"><?php _e('Birth Place', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="birth_place" name="birth_place" value="<?php echo esc_attr($existing_learner_data['birth_place']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="nationality"><?php _e('Nationality', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="nationality" name="nationality" value="<?php echo esc_attr($existing_learner_data['nationality']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="social_security"><?php _e('Social Security Number', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="social_security" name="social_security" value="<?php echo esc_attr($existing_learner_data['social_security']); ?>" style="width: 100%;" /></td>
    </tr>
    <tr>
        <th><label for="gender"><?php _e('Gender', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="gender" name="gender">
                <option value=""><?php _e('Select Gender', 'wp-cddu-manager'); ?></option>
                <option value="male" <?php selected($existing_learner_data['gender'], 'male'); ?>><?php _e('Male', 'wp-cddu-manager'); ?></option>
                <option value="female" <?php selected($existing_learner_data['gender'], 'female'); ?>><?php _e('Female', 'wp-cddu-manager'); ?></option>
                <option value="other" <?php selected($existing_learner_data['gender'], 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
</table>
