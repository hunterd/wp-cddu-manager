<?php
/**
 * Template for learner additional notes metabox
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
        <th><label for="notes"><?php _e('General Notes', 'wp-cddu-manager'); ?></label></th>
        <td><textarea id="notes" name="notes" rows="4" style="width: 100%;" placeholder="<?php _e('Any additional information about the learner...', 'wp-cddu-manager'); ?>"><?php echo esc_textarea($existing_learner_data['notes']); ?></textarea></td>
    </tr>
    <tr>
        <th><label for="medical_notes"><?php _e('Medical Notes', 'wp-cddu-manager'); ?></label></th>
        <td>
            <textarea id="medical_notes" name="medical_notes" rows="3" style="width: 100%;" placeholder="<?php _e('Any medical conditions or special considerations...', 'wp-cddu-manager'); ?>"><?php echo esc_textarea($existing_learner_data['medical_notes']); ?></textarea>
            <p class="description"><?php _e('Confidential medical information that may affect training participation.', 'wp-cddu-manager'); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="special_requirements"><?php _e('Special Requirements', 'wp-cddu-manager'); ?></label></th>
        <td>
            <textarea id="special_requirements" name="special_requirements" rows="3" style="width: 100%;" placeholder="<?php _e('Any accessibility needs, dietary restrictions, etc...', 'wp-cddu-manager'); ?>"><?php echo esc_textarea($existing_learner_data['special_requirements']); ?></textarea>
            <p class="description"><?php _e('Special accommodations needed for optimal learning experience.', 'wp-cddu-manager'); ?></p>
        </td>
    </tr>
</table>
