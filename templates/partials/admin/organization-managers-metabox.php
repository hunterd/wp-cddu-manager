<?php
/**
 * Organization managers metabox template
 * @var WP_Post $post
 * @var array $assigned_managers
 * @var WP_User[] $all_managers
 * @var array $manager_stats
 */
?>
<div id="cddu-organization-managers" class="cddu-manager-management">
    <?php if (empty($all_managers)): ?>
        <div class="cddu-empty-state">
            <div class="cddu-empty-icon">👤</div>
            <h3><?php echo esc_html__('No Managers Available', 'wp-cddu-manager'); ?></h3>
            <p><?php echo esc_html__('You need to create organization manager users before assigning them to organizations.', 'wp-cddu-manager'); ?></p>
            <p>
                <a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="button button-primary">
                    <?php echo esc_html__('Add New User', 'wp-cddu-manager'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Summary Stats -->
        <div class="cddu-manager-summary">
            <div class="cddu-stats-grid">
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($assigned_managers); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Assigned', 'wp-cddu-manager'); ?></span>
                </div>
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($all_managers) - count($assigned_managers); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Available', 'wp-cddu-manager'); ?></span>
                </div>
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($all_managers); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Total', 'wp-cddu-manager'); ?></span>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="cddu-manager-controls">
            <div class="cddu-search-section">
                <div class="cddu-search-container">
                    <input type="text" id="manager-search" placeholder="<?php echo esc_attr__('Search managers by name, email, or address...', 'wp-cddu-manager'); ?>" class="cddu-search-input" />
                    <button type="button" id="clear-manager-search" class="button button-secondary cddu-clear-btn">
                        <?php echo esc_html__('Clear', 'wp-cddu-manager'); ?>
                    </button>
                </div>
            </div>
            
            <div class="cddu-filter-section">
                <label for="manager-filter" class="cddu-filter-label"><?php echo esc_html__('Filter:', 'wp-cddu-manager'); ?></label>
                <select id="manager-filter" class="cddu-filter-select">
                    <option value="all"><?php echo esc_html__('All Managers', 'wp-cddu-manager'); ?></option>
                    <option value="assigned"><?php echo esc_html__('Assigned Only', 'wp-cddu-manager'); ?></option>
                    <option value="available"><?php echo esc_html__('Available Only', 'wp-cddu-manager'); ?></option>
                    <option value="experienced"><?php echo esc_html__('Managing Other Orgs', 'wp-cddu-manager'); ?></option>
                </select>
            </div>

            <div class="cddu-bulk-actions">
                <button type="button" id="select-all-managers" class="button button-secondary">
                    <?php echo esc_html__('Select All', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="deselect-all-managers" class="button button-secondary">
                    <?php echo esc_html__('Deselect All', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="select-visible-managers" class="button button-secondary">
                    <?php echo esc_html__('Select Visible', 'wp-cddu-manager'); ?>
                </button>
            </div>
        </div>
        
        <!-- Managers List -->
        <div class="cddu-managers-container">
            <div class="cddu-managers-list">
                <?php foreach ($all_managers as $manager): 
                    $stats = $manager_stats[$manager->ID];
                    $manager_name = $manager->display_name;
                    $is_assigned = $stats['is_assigned'];
                    $has_other_orgs = $stats['managed_organizations'] > ($is_assigned ? 1 : 0);
                    ?>
                    <div class="manager-item <?php echo $is_assigned ? 'assigned' : 'available'; ?>" 
                         data-manager-id="<?php echo esc_attr($manager->ID); ?>"
                         data-manager-name="<?php echo esc_attr(strtolower($manager_name . ' ' . $stats['email'] . ' ' . $stats['address'])); ?>"
                         data-assigned="<?php echo $is_assigned ? 'true' : 'false'; ?>"
                         data-has-other-orgs="<?php echo $has_other_orgs ? 'true' : 'false'; ?>">
                        
                        <div class="manager-checkbox">
                            <input type="checkbox" 
                                   name="organization_managers[]" 
                                   value="<?php echo esc_attr($manager->ID); ?>" 
                                   <?php checked($is_assigned); ?>
                                   id="manager-<?php echo esc_attr($manager->ID); ?>" />
                        </div>
                        
                        <div class="manager-info">
                            <div class="manager-header">
                                <label for="manager-<?php echo esc_attr($manager->ID); ?>" class="manager-name">
                                    <?php echo esc_html($manager_name); ?>
                                </label>
                                <div class="manager-badges">
                                    <?php if ($is_assigned): ?>
                                        <span class="badge badge-assigned"><?php echo esc_html__('Assigned', 'wp-cddu-manager'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($stats['managed_organizations'] > 0): ?>
                                        <span class="badge badge-organizations">
                                            <?php echo sprintf(esc_html__('%d Org(s)', 'wp-cddu-manager'), $stats['managed_organizations']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="manager-details">
                                <?php if ($stats['email']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">📧</span>
                                        <span class="detail-text"><?php echo esc_html($stats['email']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($stats['phone']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">📞</span>
                                        <span class="detail-text"><?php echo esc_html($stats['phone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($stats['address']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">📍</span>
                                        <span class="detail-text"><?php echo esc_html($stats['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="manager-actions">
                            <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $manager->ID)); ?>" 
                               class="button button-small" 
                               title="<?php echo esc_attr__('Edit Manager', 'wp-cddu-manager'); ?>"
                               target="_blank">
                                <?php echo esc_html__('Edit', 'wp-cddu-manager'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Results Info -->
        <div class="cddu-results-info">
            <span id="manager-results-count"><?php echo sprintf(esc_html__('Showing %d of %d managers', 'wp-cddu-manager'), count($all_managers), count($all_managers)); ?></span>
            <span id="manager-selected-count"><?php echo sprintf(esc_html__('%d selected', 'wp-cddu-manager'), count($assigned_managers)); ?></span>
        </div>
        
        <!-- Help Text -->
        <div class="cddu-help-text">
            <p>
                <strong><?php echo esc_html__('How to use:', 'wp-cddu-manager'); ?></strong>
                <?php echo esc_html__('Check the boxes next to managers to assign them to this organization. Use the search and filter options to find specific managers. Changes will be saved when you update the organization.', 'wp-cddu-manager'); ?>
            </p>
            <p>
                <em><?php echo esc_html__('Note: Managers can be assigned to multiple organizations.', 'wp-cddu-manager'); ?></em>
            </p>
        </div>
    <?php endif; ?>
</div>
