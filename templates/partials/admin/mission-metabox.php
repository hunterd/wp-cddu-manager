<?php
/**
 * Mission metabox template
 * @var WP_Post $post
 * @var array $meta
 * @var array $mission
 */
?>
<p>
    <label><?php echo esc_html__('Action / Title', 'wp-cddu-manager'); ?><br>
        <input type="text" name="mission[action]" value="<?php echo esc_attr($mission['action'] ?? ''); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Location', 'wp-cddu-manager'); ?><br>
        <input type="text" name="mission[location]" value="<?php echo esc_attr($mission['location'] ?? $mission['lieu'] ?? ''); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Annual hours (H_a)', 'wp-cddu-manager'); ?><br>
        <input type="number" step="0.01" name="mission[annual_hours]" value="<?php echo esc_attr($mission['annual_hours'] ?? $mission['H_a'] ?? ''); ?>" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Hourly rate', 'wp-cddu-manager'); ?><br>
        <input type="number" step="0.01" name="mission[hourly_rate]" value="<?php echo esc_attr($mission['hourly_rate'] ?? $mission['taux_horaire'] ?? ''); ?>" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Start date', 'wp-cddu-manager'); ?><br>
        <input type="date" name="mission[start_date]" value="<?php echo esc_attr($mission['start_date'] ?? $mission['date_debut'] ?? ''); ?>" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('End date', 'wp-cddu-manager'); ?><br>
        <input type="date" name="mission[end_date]" value="<?php echo esc_attr($mission['end_date'] ?? $mission['date_fin'] ?? ''); ?>" />
    </label>
</p>
