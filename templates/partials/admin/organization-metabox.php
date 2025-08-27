<?php
/**
 * Organization metabox template
 * @var WP_Post $post
 * @var array $org
 */
?>
<p>
    <label><?php echo esc_html__('Name', 'wp-cddu-manager'); ?><br>
        <input type="text" name="org[name]" value="<?php echo esc_attr($org['name'] ?? $org['denomination'] ?? $org['denom'] ?? ''); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Address', 'wp-cddu-manager'); ?><br>
        <textarea name="org[address]" style="width:100%" rows="3"><?php echo esc_textarea($org['address'] ?? $org['adresse'] ?? $org['addr'] ?? ''); ?></textarea>
    </label>
</p>
<p>
    <label><?php echo esc_html__('Legal representative', 'wp-cddu-manager'); ?><br>
        <input type="text" name="org[legal_representative]" value="<?php echo esc_attr($org['legal_representative'] ?? $org['representant'] ?? $org['representant_legal'] ?? ''); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Daily working hours', 'wp-cddu-manager'); ?><br>
        <input type="number" name="org[daily_working_hours]" id="daily_working_hours" value="<?php echo esc_attr($org['daily_working_hours'] ?? '7'); ?>" step="0.5" min="1" max="24" style="width:100%" />
        <small style="color: #666;"><?php echo esc_html__('Number of working hours per day (default: 7 hours)', 'wp-cddu-manager'); ?></small>
        <div id="daily_working_hours_message" style="margin-top: 5px; display: none;"></div>
    </label>
</p>
<p>
    <label><?php echo esc_html__('Working days per week', 'wp-cddu-manager'); ?><br>
        <input type="number" name="org[working_days_per_week]" id="working_days_per_week" value="<?php echo esc_attr($org['working_days_per_week'] ?? '5'); ?>" min="1" max="7" style="width:100%" />
        <small style="color: #666;"><?php echo esc_html__('Number of working days per week (default: 5 days)', 'wp-cddu-manager'); ?></small>
        <div id="working_days_per_week_message" style="margin-top: 5px; display: none;"></div>
    </label>
</p>
<p>
    <label><?php echo esc_html__('Holiday Calendar', 'wp-cddu-manager'); ?><br>
        <?php
        $assigned_calendar = get_post_meta($post->ID, 'holiday_calendar_id', true);
        $holiday_calendars = get_posts([
            'post_type' => 'cddu_holiday',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        ?>
        <select name="org[holiday_calendar_id]" style="width:100%">
            <option value=""><?php echo esc_html__('No holiday calendar', 'wp-cddu-manager'); ?></option>
            <?php foreach ($holiday_calendars as $calendar): ?>
                <option value="<?php echo esc_attr($calendar->ID); ?>" <?php selected($assigned_calendar, $calendar->ID); ?>>
                    <?php echo esc_html($calendar->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small style="color: #666;"><?php echo esc_html__('Select a holiday calendar for this organization', 'wp-cddu-manager'); ?></small>
    </label>
</p>
