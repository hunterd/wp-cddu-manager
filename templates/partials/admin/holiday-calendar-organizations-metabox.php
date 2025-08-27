<?php
/**
 * Holiday calendar organizations metabox template
 * @var WP_Post $post
 * @var array $assigned_organizations
 * @var array $organizations
 */
?>
<div id="holiday-calendar-organizations">
    <p class="description">
        <?php echo esc_html__('Select the organizations that will use this holiday calendar.', 'wp-cddu-manager'); ?>
    </p>
    
    <?php if (empty($organizations)): ?>
        <p><em><?php echo esc_html__('No organizations found. Please create organizations first.', 'wp-cddu-manager'); ?></em></p>
    <?php else: ?>
        <div class="organizations-list">
            <?php foreach ($organizations as $org): ?>
                <?php 
                $org_meta = get_post_meta($org->ID, 'org', true);
                $org_data = maybe_unserialize($org_meta) ?: [];
                $org_name = $org_data['name'] ?? $org_data['denomination'] ?? $org->post_title;
                ?>
                <label class="organization-item">
                    <input type="checkbox" 
                           name="assigned_organizations[]" 
                           value="<?php echo esc_attr($org->ID); ?>"
                           <?php checked(in_array($org->ID, $assigned_organizations)); ?> />
                    <span class="organization-name"><?php echo esc_html($org_name); ?></span>
                    <?php if (!empty($org_data['address'])): ?>
                        <small class="organization-address"><?php echo esc_html($org_data['address']); ?></small>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="organization-actions">
        <button type="button" id="select-all-organizations" class="button button-small">
            <?php echo esc_html__('Select All', 'wp-cddu-manager'); ?>
        </button>
        <button type="button" id="deselect-all-organizations" class="button button-small">
            <?php echo esc_html__('Deselect All', 'wp-cddu-manager'); ?>
        </button>
    </div>
</div>

<style>
.organizations-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    margin-bottom: 10px;
}

.organization-item {
    display: block;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.organization-item:last-child {
    border-bottom: none;
}

.organization-item:hover {
    background: rgba(0, 123, 255, 0.1);
}

.organization-name {
    font-weight: 600;
    display: block;
}

.organization-address {
    display: block;
    color: #666;
    font-style: italic;
    margin-top: 2px;
}

.organization-actions {
    display: flex;
    gap: 5px;
}

.organization-actions .button {
    padding: 4px 8px;
    height: auto;
    line-height: 1.4;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select all organizations
    $('#select-all-organizations').on('click', function() {
        $('.organizations-list input[type="checkbox"]').prop('checked', true);
    });
    
    // Deselect all organizations
    $('#deselect-all-organizations').on('click', function() {
        $('.organizations-list input[type="checkbox"]').prop('checked', false);
    });
    
    // Make clicking on label work properly
    $('.organization-item').on('click', function(e) {
        if (e.target.type !== 'checkbox') {
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
    });
});
</script>
