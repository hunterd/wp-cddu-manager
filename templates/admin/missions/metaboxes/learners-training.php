<?php
/**
 * Template for Learners & Training metabox
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cddu-metabox-content">
    <table class="form-table">
        <tr>
            <th><label for="training_action"><?php _e('Training Action', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <input type="text" id="training_action" name="training_action" value="<?php echo esc_attr(isset($existing_mission_data['training_action']) ? $existing_mission_data['training_action'] : ''); ?>" required placeholder="<?php _e('e.g., Advanced WordPress Development', 'wp-cddu-manager'); ?>" style="width: 100%;">
            </td>
        </tr>
        <tr>
            <th><label for="learner_ids"><?php _e('Assigned Learners', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <select id="learner_ids" name="learner_ids[]" multiple size="5" required style="width: 100%;">
                    <?php if (!empty($learners)): ?>
                        <?php 
                        $selected_learners = isset($existing_mission_data['learner_ids']) ? (array) $existing_mission_data['learner_ids'] : [];
                        ?>
                        <?php foreach ($learners as $learner): ?>
                            <option value="<?php echo esc_attr($learner->ID); ?>" <?php selected(in_array($learner->ID, $selected_learners), true); ?>>
                                <?php echo esc_html($learner->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value=""><?php _e('No learners available - Create learners first', 'wp-cddu-manager'); ?></option>
                    <?php endif; ?>
                </select>
                <br><small><?php _e('Hold Ctrl/Cmd to select multiple learners', 'wp-cddu-manager'); ?></small>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Training Modalities', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <?php 
                $selected_modalities = isset($existing_mission_data['training_modalities']) ? (array) $existing_mission_data['training_modalities'] : [];
                ?>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="in_person" <?php checked(in_array('in_person', $selected_modalities), true); ?>> <?php _e('In-Person', 'wp-cddu-manager'); ?></label>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="remote" <?php checked(in_array('remote', $selected_modalities), true); ?>> <?php _e('Remote', 'wp-cddu-manager'); ?></label>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="hybrid" <?php checked(in_array('hybrid', $selected_modalities), true); ?>> <?php _e('Hybrid', 'wp-cddu-manager'); ?></label>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="elearning" <?php checked(in_array('elearning', $selected_modalities), true); ?>> <?php _e('E-Learning', 'wp-cddu-manager'); ?></label>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="practical" <?php checked(in_array('practical', $selected_modalities), true); ?>> <?php _e('Practical Workshop', 'wp-cddu-manager'); ?></label>
                <label style="display: block; margin: 5px 0;"><input type="checkbox" name="training_modalities[]" value="theoretical" <?php checked(in_array('theoretical', $selected_modalities), true); ?>> <?php _e('Theoretical Course', 'wp-cddu-manager'); ?></label>
            </td>
        </tr>
    </table>
</div>
