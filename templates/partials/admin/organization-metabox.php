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
