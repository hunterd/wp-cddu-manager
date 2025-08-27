<?php
/**
 * Template for creating missions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Create Mission', 'wp-cddu-manager'); ?></h1>
    
    <div class="cddu-mission-form-container">
        <form id="cddu-create-mission-form" class="cddu-form">
            <?php wp_nonce_field('cddu_mission_nonce', 'cddu_mission_nonce'); ?>
            
            <div class="cddu-form-section">
                <h2><?php _e('Mission Information', 'wp-cddu-manager'); ?></h2>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="organization_id"><?php _e('Organization', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <select id="organization_id" name="organization_id" required>
                            <option value=""><?php _e('Select an organization', 'wp-cddu-manager'); ?></option>
                            <?php foreach ($organizations as $org): ?>
                                <option value="<?php echo esc_attr($org->ID); ?>">
                                    <?php echo esc_html($org->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="mission_type"><?php _e('Mission Type', 'wp-cddu-manager'); ?></label>
                        <select id="mission_type" name="mission_type">
                            <option value="standard"><?php _e('Standard', 'wp-cddu-manager'); ?></option>
                            <option value="urgent"><?php _e('Urgent', 'wp-cddu-manager'); ?></option>
                            <option value="long_term"><?php _e('Long Term', 'wp-cddu-manager'); ?></option>
                            <option value="part_time"><?php _e('Part Time', 'wp-cddu-manager'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="title"><?php _e('Mission Title', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="priority"><?php _e('Priority', 'wp-cddu-manager'); ?></label>
                        <select id="priority" name="priority">
                            <option value="low"><?php _e('Low', 'wp-cddu-manager'); ?></option>
                            <option value="medium" selected><?php _e('Medium', 'wp-cddu-manager'); ?></option>
                            <option value="high"><?php _e('High', 'wp-cddu-manager'); ?></option>
                            <option value="critical"><?php _e('Critical', 'wp-cddu-manager'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field full-width">
                        <label for="description"><?php _e('Mission Description', 'wp-cddu-manager'); ?></label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="location"><?php _e('Location', 'wp-cddu-manager'); ?></label>
                        <input type="text" id="location" name="location">
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="status"><?php _e('Status', 'wp-cddu-manager'); ?></label>
                        <select id="status" name="status">
                            <option value="draft"><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                            <option value="open"><?php _e('Open', 'wp-cddu-manager'); ?></option>
                            <option value="in_progress"><?php _e('In Progress', 'wp-cddu-manager'); ?></option>
                            <option value="completed"><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                            <option value="cancelled"><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="cddu-form-section">
                <h2><?php _e('Schedule & Budget', 'wp-cddu-manager'); ?></h2>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="start_date"><?php _e('Start Date', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="end_date"><?php _e('End Date', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="total_hours"><?php _e('Total Hours', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="number" id="total_hours" name="total_hours" step="0.5" min="0" required>
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="hourly_rate"><?php _e('Hourly Rate (€)', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="cddu-mission-calculations">
                    <div class="calculation-item">
                        <span class="label"><?php _e('Duration:', 'wp-cddu-manager'); ?></span>
                        <span id="mission-duration" class="value">-</span>
                    </div>
                    <div class="calculation-item">
                        <span class="label"><?php _e('Total Budget:', 'wp-cddu-manager'); ?></span>
                        <span id="mission-total-budget" class="value">-</span>
                    </div>
                    <div class="calculation-item">
                        <span class="label"><?php _e('Hours per Day:', 'wp-cddu-manager'); ?></span>
                        <span id="mission-hours-per-day" class="value">-</span>
                    </div>
                </div>
            </div>
            
            <div class="cddu-form-section">
                <h2><?php _e('Learners & Training', 'wp-cddu-manager'); ?></h2>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="training_action"><?php _e('Training Action', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <input type="text" id="training_action" name="training_action" required placeholder="<?php _e('e.g., Advanced WordPress Development', 'wp-cddu-manager'); ?>">
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field">
                        <label for="learner_ids"><?php _e('Assigned Learners', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <select id="learner_ids" name="learner_ids[]" multiple size="5" required>
                            <?php if (!empty($learners)): ?>
                                <?php foreach ($learners as $learner): ?>
                                    <option value="<?php echo esc_attr($learner->ID); ?>">
                                        <?php echo esc_html($learner->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value=""><?php _e('No learners available - Create learners first', 'wp-cddu-manager'); ?></option>
                            <?php endif; ?>
                        </select>
                        <small><?php _e('Hold Ctrl/Cmd to select multiple learners', 'wp-cddu-manager'); ?></small>
                    </div>
                    
                    <div class="cddu-form-field">
                        <label for="training_modalities"><?php _e('Training Modalities', 'wp-cddu-manager'); ?> <span class="required">*</span></label>
                        <div class="modality-checkboxes">
                            <label><input type="checkbox" name="training_modalities[]" value="in_person"> <?php _e('In-Person', 'wp-cddu-manager'); ?></label>
                            <label><input type="checkbox" name="training_modalities[]" value="remote"> <?php _e('Remote', 'wp-cddu-manager'); ?></label>
                            <label><input type="checkbox" name="training_modalities[]" value="hybrid"> <?php _e('Hybrid', 'wp-cddu-manager'); ?></label>
                            <label><input type="checkbox" name="training_modalities[]" value="elearning"> <?php _e('E-Learning', 'wp-cddu-manager'); ?></label>
                            <label><input type="checkbox" name="training_modalities[]" value="practical"> <?php _e('Practical Workshop', 'wp-cddu-manager'); ?></label>
                            <label><input type="checkbox" name="training_modalities[]" value="theoretical"> <?php _e('Theoretical Course', 'wp-cddu-manager'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="cddu-form-row">
                    <div class="cddu-form-field full-width">
                        <a href="<?php echo admin_url('post-new.php?post_type=cddu_learner'); ?>" class="button button-secondary" target="_blank">
                            <?php _e('Add New Learner', 'wp-cddu-manager'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="cddu-form-section">
                <h2><?php _e('Required Skills', 'wp-cddu-manager'); ?></h2>
                
                <div class="cddu-skills-container">
                    <div class="cddu-form-field">
                        <label for="new_skill"><?php _e('Add Skill', 'wp-cddu-manager'); ?></label>
                        <div class="skill-input-container">
                            <input type="text" id="new_skill" placeholder="<?php _e('Enter a required skill', 'wp-cddu-manager'); ?>">
                            <button type="button" id="add-skill-btn" class="button"><?php _e('Add', 'wp-cddu-manager'); ?></button>
                        </div>
                    </div>
                    
                    <div class="skills-list" id="skills-list">
                        <!-- Skills will be added here dynamically -->
                    </div>
                </div>
            </div>
            
            <div class="cddu-form-actions">
                <button type="button" id="validate-mission-btn" class="button button-secondary">
                    <?php _e('Validate', 'wp-cddu-manager'); ?>
                </button>
                <button type="submit" id="create-mission-btn" class="button button-primary">
                    <?php _e('Create Mission', 'wp-cddu-manager'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div id="cddu-mission-messages" class="cddu-messages"></div>
</div>

<?php
// Enqueue the mission form CSS file
wp_enqueue_style(
    'cddu-mission-form',
    plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/mission-form.css',
    array(),
    '1.0.0'
);
?>
