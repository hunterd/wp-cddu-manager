<?php
/**
 * Mission metabox template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get mission data
$mission_data = $mission ?: [];

// Default values
$defaults = [
    'organization_id' => '',
    'mission_type' => 'standard',
    'priority' => 'medium',
    'location' => '',
    'start_date' => '',
    'end_date' => '',
    'total_hours' => '',
    'hourly_rate' => '',
    'required_skills' => [],
    'status' => 'draft',
];

$mission_data = array_merge($defaults, $mission_data);
?>

<div class="cddu-mission-metabox">
    <?php wp_nonce_field('cddu_mission_meta_nonce', 'cddu_mission_meta_nonce'); ?>
    
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="mission_organization_id"><?php _e('Organization', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <select name="mission[organization_id]" id="mission_organization_id" class="regular-text">
                        <option value=""><?php _e('Select an organization', 'wp-cddu-manager'); ?></option>
                        <?php
                        $organizations = get_posts([
                            'post_type' => 'cddu_organization',
                            'numberposts' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);
                        
                        foreach ($organizations as $org):
                        ?>
                            <option value="<?php echo esc_attr($org->ID); ?>" <?php selected($mission_data['organization_id'], $org->ID); ?>>
                                <?php echo esc_html($org->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_type"><?php _e('Mission Type', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <select name="mission[mission_type]" id="mission_type" class="regular-text">
                        <option value="standard" <?php selected($mission_data['mission_type'], 'standard'); ?>><?php _e('Standard', 'wp-cddu-manager'); ?></option>
                        <option value="urgent" <?php selected($mission_data['mission_type'], 'urgent'); ?>><?php _e('Urgent', 'wp-cddu-manager'); ?></option>
                        <option value="long_term" <?php selected($mission_data['mission_type'], 'long_term'); ?>><?php _e('Long Term', 'wp-cddu-manager'); ?></option>
                        <option value="part_time" <?php selected($mission_data['mission_type'], 'part_time'); ?>><?php _e('Part Time', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_priority"><?php _e('Priority', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <select name="mission[priority]" id="mission_priority" class="regular-text">
                        <option value="low" <?php selected($mission_data['priority'], 'low'); ?>><?php _e('Low', 'wp-cddu-manager'); ?></option>
                        <option value="medium" <?php selected($mission_data['priority'], 'medium'); ?>><?php _e('Medium', 'wp-cddu-manager'); ?></option>
                        <option value="high" <?php selected($mission_data['priority'], 'high'); ?>><?php _e('High', 'wp-cddu-manager'); ?></option>
                        <option value="critical" <?php selected($mission_data['priority'], 'critical'); ?>><?php _e('Critical', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_status"><?php _e('Status', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <select name="mission[status]" id="mission_status" class="regular-text">
                        <option value="draft" <?php selected($mission_data['status'], 'draft'); ?>><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                        <option value="open" <?php selected($mission_data['status'], 'open'); ?>><?php _e('Open', 'wp-cddu-manager'); ?></option>
                        <option value="in_progress" <?php selected($mission_data['status'], 'in_progress'); ?>><?php _e('In Progress', 'wp-cddu-manager'); ?></option>
                        <option value="completed" <?php selected($mission_data['status'], 'completed'); ?>><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                        <option value="cancelled" <?php selected($mission_data['status'], 'cancelled'); ?>><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_location"><?php _e('Location', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <input type="text" name="mission[location]" id="mission_location" value="<?php echo esc_attr($mission_data['location']); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_start_date"><?php _e('Start Date', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <input type="date" name="mission[start_date]" id="mission_start_date" value="<?php echo esc_attr($mission_data['start_date']); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_end_date"><?php _e('End Date', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <input type="date" name="mission[end_date]" id="mission_end_date" value="<?php echo esc_attr($mission_data['end_date']); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_total_hours"><?php _e('Total Hours', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <input type="number" name="mission[total_hours]" id="mission_total_hours" value="<?php echo esc_attr($mission_data['total_hours']); ?>" class="regular-text" step="0.5" min="0" />
                    <p class="description"><?php _e('Total number of hours for this mission', 'wp-cddu-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_hourly_rate"><?php _e('Hourly Rate (€)', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <input type="number" name="mission[hourly_rate]" id="mission_hourly_rate" value="<?php echo esc_attr($mission_data['hourly_rate']); ?>" class="regular-text" step="0.01" min="0" />
                    <p class="description"><?php _e('Hourly rate in euros', 'wp-cddu-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="mission_required_skills"><?php _e('Required Skills', 'wp-cddu-manager'); ?></label>
                </th>
                <td>
                    <div id="skills-manager">
                        <div class="skill-input-row">
                            <input type="text" id="new_skill_input" placeholder="<?php _e('Add a required skill', 'wp-cddu-manager'); ?>" class="regular-text" />
                            <button type="button" id="add_skill_btn" class="button button-secondary"><?php _e('Add', 'wp-cddu-manager'); ?></button>
                        </div>
                        
                        <div id="skills_list" class="skills-list">
                            <?php if (!empty($mission_data['required_skills']) && is_array($mission_data['required_skills'])): ?>
                                <?php foreach ($mission_data['required_skills'] as $skill): ?>
                                    <span class="skill-tag">
                                        <?php echo esc_html($skill); ?>
                                        <span class="remove-skill" data-skill="<?php echo esc_attr($skill); ?>">&times;</span>
                                        <input type="hidden" name="mission[required_skills][]" value="<?php echo esc_attr($skill); ?>" />
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="description"><?php _e('Add skills required for this mission', 'wp-cddu-manager'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="mission-calculations" id="mission_calculations">
        <h4><?php _e('Mission Calculations', 'wp-cddu-manager'); ?></h4>
        <div class="calc-row">
            <span class="label"><?php _e('Duration:', 'wp-cddu-manager'); ?></span>
            <span class="value" id="calc_duration">-</span>
        </div>
        <div class="calc-row">
            <span class="label"><?php _e('Total Budget:', 'wp-cddu-manager'); ?></span>
            <span class="value" id="calc_budget">-</span>
        </div>
        <div class="calc-row">
            <span class="label"><?php _e('Hours per Day:', 'wp-cddu-manager'); ?></span>
            <span class="value" id="calc_hours_per_day">-</span>
        </div>
    </div>
</div>
