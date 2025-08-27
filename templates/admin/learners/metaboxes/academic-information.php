<?php
/**
 * Template for learner academic information metabox
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
        <th><label for="level"><?php _e('Current Level', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="level" name="level">
                <option value=""><?php _e('Select Level', 'wp-cddu-manager'); ?></option>
                <option value="beginner" <?php selected($existing_learner_data['level'], 'beginner'); ?>><?php _e('Beginner', 'wp-cddu-manager'); ?></option>
                <option value="intermediate" <?php selected($existing_learner_data['level'], 'intermediate'); ?>><?php _e('Intermediate', 'wp-cddu-manager'); ?></option>
                <option value="advanced" <?php selected($existing_learner_data['level'], 'advanced'); ?>><?php _e('Advanced', 'wp-cddu-manager'); ?></option>
                <option value="expert" <?php selected($existing_learner_data['level'], 'expert'); ?>><?php _e('Expert', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th><label for="diploma"><?php _e('Highest Diploma', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="diploma" name="diploma" style="width: 100%;">
                <option value=""><?php _e('Select Diploma', 'wp-cddu-manager'); ?></option>
                <option value="none" <?php selected($existing_learner_data['diploma'], 'none'); ?>><?php _e('No Diploma', 'wp-cddu-manager'); ?></option>
                <option value="cap" <?php selected($existing_learner_data['diploma'], 'cap'); ?>><?php _e('CAP', 'wp-cddu-manager'); ?></option>
                <option value="bep" <?php selected($existing_learner_data['diploma'], 'bep'); ?>><?php _e('BEP', 'wp-cddu-manager'); ?></option>
                <option value="bac" <?php selected($existing_learner_data['diploma'], 'bac'); ?>><?php _e('Baccalauréat', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_2" <?php selected($existing_learner_data['diploma'], 'bac_plus_2'); ?>><?php _e('Bac+2 (BTS/DUT)', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_3" <?php selected($existing_learner_data['diploma'], 'bac_plus_3'); ?>><?php _e('Bac+3 (Licence)', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_5" <?php selected($existing_learner_data['diploma'], 'bac_plus_5'); ?>><?php _e('Bac+5 (Master)', 'wp-cddu-manager'); ?></option>
                <option value="bac_plus_8" <?php selected($existing_learner_data['diploma'], 'bac_plus_8'); ?>><?php _e('Bac+8 (Doctorat)', 'wp-cddu-manager'); ?></option>
                <option value="other" <?php selected($existing_learner_data['diploma'], 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th><label for="specialization"><?php _e('Specialization', 'wp-cddu-manager'); ?></label></th>
        <td><input type="text" id="specialization" name="specialization" value="<?php echo esc_attr($existing_learner_data['specialization']); ?>" style="width: 100%;" placeholder="<?php _e('e.g., Computer Science, Marketing, etc.', 'wp-cddu-manager'); ?>" /></td>
    </tr>
    <tr>
        <th><label for="previous_experience"><?php _e('Previous Experience', 'wp-cddu-manager'); ?></label></th>
        <td><textarea id="previous_experience" name="previous_experience" rows="4" style="width: 100%;" placeholder="<?php _e('Describe any relevant work experience or training...', 'wp-cddu-manager'); ?>"><?php echo esc_textarea($existing_learner_data['previous_experience']); ?></textarea></td>
    </tr>
    <tr>
        <th><label for="skills"><?php _e('Skills', 'wp-cddu-manager'); ?></label></th>
        <td>
            <div id="skills-container">
                <?php 
                $skills = is_array($existing_learner_data['skills']) ? $existing_learner_data['skills'] : [];
                if (empty($skills)) {
                    $skills = [''];
                }
                foreach ($skills as $index => $skill): ?>
                    <div class="skill-row" style="margin-bottom: 5px;">
                        <input type="text" name="skills[]" value="<?php echo esc_attr($skill); ?>" style="width: 85%;" placeholder="<?php _e('Enter a skill...', 'wp-cddu-manager'); ?>" />
                        <button type="button" class="button remove-skill" style="margin-left: 5px;"><?php _e('Remove', 'wp-cddu-manager'); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-skill" class="button"><?php _e('Add Skill', 'wp-cddu-manager'); ?></button>
        </td>
    </tr>
    <tr>
        <th><label for="status"><?php _e('Status', 'wp-cddu-manager'); ?></label></th>
        <td>
            <select id="status" name="status">
                <option value="active" <?php selected($existing_learner_data['status'], 'active'); ?>><?php _e('Active', 'wp-cddu-manager'); ?></option>
                <option value="inactive" <?php selected($existing_learner_data['status'], 'inactive'); ?>><?php _e('Inactive', 'wp-cddu-manager'); ?></option>
                <option value="graduated" <?php selected($existing_learner_data['status'], 'graduated'); ?>><?php _e('Graduated', 'wp-cddu-manager'); ?></option>
                <option value="suspended" <?php selected($existing_learner_data['status'], 'suspended'); ?>><?php _e('Suspended', 'wp-cddu-manager'); ?></option>
            </select>
        </td>
    </tr>
</table>

<script>
jQuery(document).ready(function($) {
    // Add skill functionality
    $('#add-skill').on('click', function() {
        var newSkillRow = '<div class="skill-row" style="margin-bottom: 5px;">' +
            '<input type="text" name="skills[]" value="" style="width: 85%;" placeholder="<?php _e('Enter a skill...', 'wp-cddu-manager'); ?>" />' +
            '<button type="button" class="button remove-skill" style="margin-left: 5px;"><?php _e('Remove', 'wp-cddu-manager'); ?></button>' +
            '</div>';
        $('#skills-container').append(newSkillRow);
    });
    
    // Remove skill functionality
    $(document).on('click', '.remove-skill', function() {
        if ($('.skill-row').length > 1) {
            $(this).closest('.skill-row').remove();
        }
    });
});
</script>
