<?php
/**
 * Template for managing missions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Manage Missions', 'wp-cddu-manager'); ?></h1>
    <a href="<?php echo admin_url('edit.php?post_type=cddu_mission&page=create-mission'); ?>" class="page-title-action">
        <?php _e('Add New Mission', 'wp-cddu-manager'); ?>
    </a>
    
    <div class="cddu-missions-container">
        <div class="cddu-filters">
            <div class="filter-group">
                <label for="filter-organization"><?php _e('Filter by Organization:', 'wp-cddu-manager'); ?></label>
                <select id="filter-organization">
                    <option value=""><?php _e('All Organizations', 'wp-cddu-manager'); ?></option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo esc_attr($org->ID); ?>">
                            <?php echo esc_html($org->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filter-status"><?php _e('Filter by Status:', 'wp-cddu-manager'); ?></label>
                <select id="filter-status">
                    <option value=""><?php _e('All Status', 'wp-cddu-manager'); ?></option>
                    <option value="draft"><?php _e('Draft', 'wp-cddu-manager'); ?></option>
                    <option value="open"><?php _e('Open', 'wp-cddu-manager'); ?></option>
                    <option value="in_progress"><?php _e('In Progress', 'wp-cddu-manager'); ?></option>
                    <option value="completed"><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                    <option value="cancelled"><?php _e('Cancelled', 'wp-cddu-manager'); ?></option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filter-priority"><?php _e('Filter by Priority:', 'wp-cddu-manager'); ?></label>
                <select id="filter-priority">
                    <option value=""><?php _e('All Priorities', 'wp-cddu-manager'); ?></option>
                    <option value="low"><?php _e('Low', 'wp-cddu-manager'); ?></option>
                    <option value="medium"><?php _e('Medium', 'wp-cddu-manager'); ?></option>
                    <option value="high"><?php _e('High', 'wp-cddu-manager'); ?></option>
                    <option value="critical"><?php _e('Critical', 'wp-cddu-manager'); ?></option>
                </select>
            </div>
            
            <button type="button" id="apply-filters" class="button button-secondary">
                <?php _e('Apply Filters', 'wp-cddu-manager'); ?>
            </button>
            <button type="button" id="clear-filters" class="button">
                <?php _e('Clear', 'wp-cddu-manager'); ?>
            </button>
        </div>
        
        <div class="cddu-missions-stats">
            <div class="stat-item">
                <span class="stat-number" id="total-missions"><?php echo count($missions); ?></span>
                <span class="stat-label"><?php _e('Total Missions', 'wp-cddu-manager'); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="open-missions">
                    <?php 
                    echo count(array_filter($missions, function($mission) {
                        return get_post_meta($mission->ID, 'mission_status', true) === 'open';
                    }));
                    ?>
                </span>
                <span class="stat-label"><?php _e('Open Missions', 'wp-cddu-manager'); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="in-progress-missions">
                    <?php 
                    echo count(array_filter($missions, function($mission) {
                        return get_post_meta($mission->ID, 'mission_status', true) === 'in_progress';
                    }));
                    ?>
                </span>
                <span class="stat-label"><?php _e('In Progress', 'wp-cddu-manager'); ?></span>
            </div>
        </div>
        
        <div class="cddu-missions-table-container">
            <table class="wp-list-table widefat fixed striped" id="missions-table">
                <thead>
                    <tr>
                        <th scope="col" class="sortable" data-sort="title">
                            <?php _e('Mission Title', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="organization">
                            <?php _e('Organization', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="status">
                            <?php _e('Status', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="priority">
                            <?php _e('Priority', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="start_date">
                            <?php _e('Start Date', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="end_date">
                            <?php _e('End Date', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="total_hours">
                            <?php _e('Total Hours', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="sortable" data-sort="budget">
                            <?php _e('Budget', 'wp-cddu-manager'); ?>
                            <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col"><?php _e('Actions', 'wp-cddu-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($missions)): ?>
                        <?php foreach ($missions as $mission): 
                            $mission_meta = get_post_meta($mission->ID, 'mission', true);
                            $mission_data = maybe_unserialize($mission_meta) ?: [];
                            $organization_id = get_post_meta($mission->ID, 'organization_id', true);
                            $organization = $organization_id ? get_post($organization_id) : null;
                            $mission_status = get_post_meta($mission->ID, 'mission_status', true) ?: 'draft';
                            $priority = $mission_data['priority'] ?? 'medium';
                            $total_hours = $mission_data['total_hours'] ?? 0;
                            $hourly_rate = $mission_data['hourly_rate'] ?? 0;
                            $total_budget = $total_hours * $hourly_rate;
                        ?>
                            <tr data-mission-id="<?php echo esc_attr($mission->ID); ?>" 
                                data-organization-id="<?php echo esc_attr($organization_id); ?>"
                                data-status="<?php echo esc_attr($mission_status); ?>"
                                data-priority="<?php echo esc_attr($priority); ?>">
                                <td class="mission-title">
                                    <strong><?php echo esc_html($mission->post_title); ?></strong>
                                    <?php if (!empty($mission_data['location'])): ?>
                                        <br><small class="location"><?php echo esc_html($mission_data['location']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="organization">
                                    <?php echo $organization ? esc_html($organization->post_title) : __('Unknown', 'wp-cddu-manager'); ?>
                                </td>
                                <td class="status">
                                    <span class="status-badge status-<?php echo esc_attr($mission_status); ?>">
                                        <?php 
                                        switch($mission_status) {
                                            case 'draft': _e('Draft', 'wp-cddu-manager'); break;
                                            case 'open': _e('Open', 'wp-cddu-manager'); break;
                                            case 'in_progress': _e('In Progress', 'wp-cddu-manager'); break;
                                            case 'completed': _e('Completed', 'wp-cddu-manager'); break;
                                            case 'cancelled': _e('Cancelled', 'wp-cddu-manager'); break;
                                            default: echo esc_html($mission_status);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="priority">
                                    <span class="priority-badge priority-<?php echo esc_attr($priority); ?>">
                                        <?php 
                                        switch($priority) {
                                            case 'low': _e('Low', 'wp-cddu-manager'); break;
                                            case 'medium': _e('Medium', 'wp-cddu-manager'); break;
                                            case 'high': _e('High', 'wp-cddu-manager'); break;
                                            case 'critical': _e('Critical', 'wp-cddu-manager'); break;
                                            default: echo esc_html($priority);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="start-date" data-sort-value="<?php echo esc_attr($mission_data['start_date'] ?? ''); ?>">
                                    <?php 
                                    if (!empty($mission_data['start_date'])) {
                                        echo esc_html(date_i18n(get_option('date_format'), strtotime($mission_data['start_date'])));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="end-date" data-sort-value="<?php echo esc_attr($mission_data['end_date'] ?? ''); ?>">
                                    <?php 
                                    if (!empty($mission_data['end_date'])) {
                                        echo esc_html(date_i18n(get_option('date_format'), strtotime($mission_data['end_date'])));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="total-hours" data-sort-value="<?php echo esc_attr($total_hours); ?>">
                                    <?php echo esc_html(number_format($total_hours, 1)); ?>h
                                </td>
                                <td class="budget" data-sort-value="<?php echo esc_attr($total_budget); ?>">
                                    <?php echo esc_html(number_format($total_budget, 2)); ?>€
                                </td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo admin_url('post.php?post=' . $mission->ID . '&action=edit'); ?>">
                                                <?php _e('Edit', 'wp-cddu-manager'); ?>
                                            </a>
                                        </span>
                                        <span class="view">
                                            | <a href="#" class="view-mission" data-mission-id="<?php echo esc_attr($mission->ID); ?>">
                                                <?php _e('View', 'wp-cddu-manager'); ?>
                                            </a>
                                        </span>
                                        <span class="duplicate">
                                            | <a href="#" class="duplicate-mission" data-mission-id="<?php echo esc_attr($mission->ID); ?>">
                                                <?php _e('Duplicate', 'wp-cddu-manager'); ?>
                                            </a>
                                        </span>
                                        <span class="delete">
                                            | <a href="#" class="delete-mission" data-mission-id="<?php echo esc_attr($mission->ID); ?>">
                                                <?php _e('Delete', 'wp-cddu-manager'); ?>
                                            </a>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-missions">
                                <?php _e('No missions found.', 'wp-cddu-manager'); ?>
                                <a href="<?php echo admin_url('edit.php?post_type=cddu_mission&page=create-mission'); ?>">
                                    <?php _e('Create your first mission', 'wp-cddu-manager'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="cddu-mission-modal" class="cddu-modal" style="display: none;">
        <div class="cddu-modal-content">
            <div class="cddu-modal-header">
                <h2 id="modal-title"><?php _e('Mission Details', 'wp-cddu-manager'); ?></h2>
                <span class="cddu-modal-close">&times;</span>
            </div>
            <div class="cddu-modal-body" id="modal-body">
                <!-- Mission details will be loaded here -->
            </div>
        </div>
    </div>
    
    <div id="cddu-mission-messages" class="cddu-messages"></div>
</div>

