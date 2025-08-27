<?php
/**
 * Template for Schedule & Budget metabox
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cddu-metabox-content">
    <table class="form-table">
        <tr>
            <th><label for="start_date"><?php _e('Start Date', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr(isset($existing_mission_data['start_date']) ? $existing_mission_data['start_date'] : ''); ?>" required>
            </td>
        </tr>
        <tr>
            <th><label for="end_date"><?php _e('End Date', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr(isset($existing_mission_data['end_date']) ? $existing_mission_data['end_date'] : ''); ?>" required>
            </td>
        </tr>
        <tr>
            <th><label for="total_hours"><?php _e('Total Hours', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <input type="number" id="total_hours" name="total_hours" step="0.5" min="0" value="<?php echo esc_attr(isset($existing_mission_data['total_hours']) ? $existing_mission_data['total_hours'] : ''); ?>" required>
            </td>
        </tr>
        <tr>
            <th><label for="hourly_rate"><?php _e('Hourly Rate (€)', 'wp-cddu-manager'); ?> <span class="required">*</span></label></th>
            <td>
                <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" value="<?php echo esc_attr(isset($existing_mission_data['hourly_rate']) ? $existing_mission_data['hourly_rate'] : ''); ?>" required>
            </td>
        </tr>
    </table>
    
    <div class="cddu-mission-calculations" style="background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px;">
        <h4><?php _e('Calculations', 'wp-cddu-manager'); ?></h4>
        <p>
            <strong><?php _e('Duration:', 'wp-cddu-manager'); ?></strong> 
            <span id="mission-duration">-</span> |
            <strong><?php _e('Total Budget:', 'wp-cddu-manager'); ?></strong> 
            <span id="mission-total-budget">-</span> |
            <strong><?php _e('Hours per Day:', 'wp-cddu-manager'); ?></strong> 
            <span id="mission-hours-per-day">-</span>
        </p>
        <p>
            <strong><?php _e('Working Days:', 'wp-cddu-manager'); ?></strong> 
            <span id="mission-working-days">-</span> |
            <strong><?php _e('Daily Working Hours (Org):', 'wp-cddu-manager'); ?></strong> 
            <span id="mission-daily-working-hours">-</span>
        </p>
    </div>
</div>
