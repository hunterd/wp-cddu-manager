<?php
/**
 * Organization instructors metabox template
 * @var WP_Post $post
 * @var array $assigned_instructors
 * @var WP_User[] $all_instructors
 * @var array $instructor_stats
 */
?>
<div id="cddu-organization-instructors" class="cddu-instructor-management">
    <?php if (empty($all_instructors)): ?>
        <div class="cddu-empty-state">
            <div class="cddu-empty-icon">👥</div>
            <h3><?php echo esc_html__('No Instructors Available', 'wp-cddu-manager'); ?></h3>
            <p><?php echo esc_html__('You need to create instructor users before assigning them to organizations.', 'wp-cddu-manager'); ?></p>
            <p>
                <a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="button button-primary">
                    <?php echo esc_html__('Add New User', 'wp-cddu-manager'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Summary Stats -->
        <div class="cddu-instructor-summary">
            <div class="cddu-stats-grid">
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($assigned_instructors); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Assigned', 'wp-cddu-manager'); ?></span>
                </div>
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($all_instructors) - count($assigned_instructors); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Available', 'wp-cddu-manager'); ?></span>
                </div>
                <div class="cddu-stat-item">
                    <span class="cddu-stat-number"><?php echo count($all_instructors); ?></span>
                    <span class="cddu-stat-label"><?php echo esc_html__('Total', 'wp-cddu-manager'); ?></span>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="cddu-instructor-controls">
            <div class="cddu-search-section">
                <div class="cddu-search-container">
                    <input type="text" id="instructor-search" placeholder="<?php echo esc_attr__('Search instructors by name, email, or address...', 'wp-cddu-manager'); ?>" class="cddu-search-input" />
                    <button type="button" id="clear-search" class="button button-secondary cddu-clear-btn">
                        <?php echo esc_html__('Clear', 'wp-cddu-manager'); ?>
                    </button>
                </div>
            </div>
            
            <div class="cddu-filter-section">
                <label for="instructor-filter" class="cddu-filter-label"><?php echo esc_html__('Filter:', 'wp-cddu-manager'); ?></label>
                <select id="instructor-filter" class="cddu-filter-select">
                    <option value="all"><?php echo esc_html__('All Instructors', 'wp-cddu-manager'); ?></option>
                    <option value="assigned"><?php echo esc_html__('Assigned Only', 'wp-cddu-manager'); ?></option>
                    <option value="available"><?php echo esc_html__('Available Only', 'wp-cddu-manager'); ?></option>
                    <option value="with-contracts"><?php echo esc_html__('With Active Contracts', 'wp-cddu-manager'); ?></option>
                </select>
            </div>

            <div class="cddu-bulk-actions">
                <button type="button" id="select-all-instructors" class="button button-secondary">
                    <?php echo esc_html__('Select All', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="deselect-all-instructors" class="button button-secondary">
                    <?php echo esc_html__('Deselect All', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="select-visible" class="button button-secondary">
                    <?php echo esc_html__('Select Visible', 'wp-cddu-manager'); ?>
                </button>
            </div>
        </div>
        
        <!-- Instructors List -->
        <div class="cddu-instructors-container">
            <div class="cddu-instructors-list">
                <?php foreach ($all_instructors as $instructor): 
                    $stats = $instructor_stats[$instructor->ID];
                    $instructor_name = $instructor->display_name;
                    $is_assigned = $stats['is_assigned'];
                    $has_contracts = $stats['active_contracts'] > 0;
                    $cannot_unassign = $is_assigned && $has_contracts;
                    ?>
                    <div class="instructor-item <?php echo $is_assigned ? 'assigned' : 'available'; ?>" 
                         data-instructor-id="<?php echo esc_attr($instructor->ID); ?>"
                         data-instructor-name="<?php echo esc_attr(strtolower($instructor_name . ' ' . $stats['email'] . ' ' . $stats['address'])); ?>"
                         data-assigned="<?php echo $is_assigned ? 'true' : 'false'; ?>"
                         data-has-contracts="<?php echo $has_contracts ? 'true' : 'false'; ?>">
                        
                        <div class="instructor-checkbox">
                            <input type="checkbox" 
                                   name="assigned_instructors[]" 
                                   value="<?php echo esc_attr($instructor->ID); ?>" 
                                   <?php checked($is_assigned); ?>
                                   id="instructor-<?php echo esc_attr($instructor->ID); ?>" />
                        </div>
                        
                        <div class="instructor-info">
                            <div class="instructor-header">
                                <label for="instructor-<?php echo esc_attr($instructor->ID); ?>" class="instructor-name">
                                    <?php echo esc_html($instructor_name); ?>
                                </label>
                                <div class="instructor-badges">
                                    <?php if ($is_assigned): ?>
                                        <span class="badge badge-assigned"><?php echo esc_html__('Assigned', 'wp-cddu-manager'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($stats['active_contracts'] > 0): ?>
                                        <span class="badge badge-contracts">
                                            <?php echo sprintf(esc_html__('%d Contract(s)', 'wp-cddu-manager'), $stats['active_contracts']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="instructor-details">
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
                        
                        <div class="instructor-actions">
                            <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $instructor->ID)); ?>" 
                               class="button button-small" 
                               title="<?php echo esc_attr__('Edit Instructor', 'wp-cddu-manager'); ?>"
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
            <span id="results-count"><?php echo sprintf(esc_html__('Showing %d of %d instructors', 'wp-cddu-manager'), count($all_instructors), count($all_instructors)); ?></span>
            <span id="selected-count"><?php echo sprintf(esc_html__('%d selected', 'wp-cddu-manager'), count($assigned_instructors)); ?></span>
        </div>
        
        <!-- Help Text -->
        <div class="cddu-help-text">
            <p>
                <strong><?php echo esc_html__('How to use:', 'wp-cddu-manager'); ?></strong>
                <?php echo esc_html__('Check the boxes next to instructors to assign them to this organization. Use the search and filter options to find specific instructors. Changes will be saved when you update the organization.', 'wp-cddu-manager'); ?>
            </p>
            <p>
                <em><?php echo esc_html__('Note: Instructors with active contracts cannot be unassigned without canceling those contracts first.', 'wp-cddu-manager'); ?></em>
            </p>
        </div>
    <?php endif; ?>
</div>
