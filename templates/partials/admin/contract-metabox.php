<?php
/**
 * Contract metabox template
 * @var WP_Post $post
 * @var array $meta
 * @var array $org
 * @var array $formateur
 * @var array $mission
 * @var string $instructor_user_id
 * @var WP_User[] $instructor_users
 */
?>
<p>
    <label><?php echo esc_html__('Organization - Name', 'wp-cddu-manager'); ?><br>
        <input type="text" name="org[name]" value="<?php echo esc_attr($org['name'] ?? $org['denomination'] ?? ''); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Instructor', 'wp-cddu-manager'); ?><br>
        <select name="instructor_user_id" style="width:100%">
            <option value=""><?php echo esc_html__('-- Select instructor --', 'wp-cddu-manager'); ?></option>
            <?php foreach ($instructor_users as $user): ?>
                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($instructor_user_id, $user->ID); ?>>
                    <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
</p>
<p>
    <label><?php echo esc_html__('Legacy Instructor Name (for old contracts)', 'wp-cddu-manager'); ?><br>
        <input type="text" name="instructor[full_name]" value="<?php echo esc_attr($formateur['full_name'] ?? $formateur['nom_prenom'] ?? ($formateur['prenom'] ?? '') . ' ' . ($formateur['nom'] ?? '')); ?>" style="width:100%" />
    </label>
</p>
<p>
    <label><?php echo esc_html__('Mission - Annual hours (H_a)', 'wp-cddu-manager'); ?><br>
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
