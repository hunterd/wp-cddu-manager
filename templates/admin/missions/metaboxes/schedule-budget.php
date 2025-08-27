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
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Mission calculations
    function calculateMissionStats() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const totalHours = parseFloat($('#total_hours').val()) || 0;
        const hourlyRate = parseFloat($('#hourly_rate').val()) || 0;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = Math.abs(end.getTime() - start.getTime());
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            
            $('#mission-duration').text(daysDiff + ' <?php _e("days", "wp-cddu-manager"); ?>');
            
            if (totalHours > 0) {
                const hoursPerDay = (totalHours / daysDiff).toFixed(1);
                $('#mission-hours-per-day').text(hoursPerDay + ' <?php _e("hours/day", "wp-cddu-manager"); ?>');
            }
        }
        
        if (totalHours > 0 && hourlyRate > 0) {
            const totalBudget = totalHours * hourlyRate;
            $('#mission-total-budget').text(totalBudget.toFixed(2) + ' €');
        }
    }
    
    $('#start_date, #end_date, #total_hours, #hourly_rate').on('change keyup', calculateMissionStats);
    
    // Initial calculation
    calculateMissionStats();
});
</script>
