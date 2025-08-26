<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="cddu-instructor-dashboard">
    <div class="dashboard-header">
        <div class="container">
            <h1><?php echo esc_html__('Submit Monthly Timesheet', 'wp-cddu-manager'); ?></h1>
            <div class="user-info">
                <?php echo esc_html__('Welcome', 'wp-cddu-manager'); ?>, <strong><?php echo esc_html($current_user->display_name); ?></strong>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="logout-link"><?php echo esc_html__('Logout', 'wp-cddu-manager'); ?></a>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-nav">
                <a href="<?php echo home_url('/instructor-dashboard/'); ?>" class="nav-link">
                    <?php echo esc_html__('Dashboard', 'wp-cddu-manager'); ?>
                </a>
                <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="nav-link active">
                    <?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?>
                </a>
            </div>

            <div class="dashboard-main">
                <div class="timesheet-form-section">
                    <h2><?php echo esc_html__('Submit Monthly Hours', 'wp-cddu-manager'); ?></h2>
                    
                    <form id="timesheet-form">
                        <?php wp_nonce_field('cddu_instructor_nonce', 'timesheet_nonce'); ?>
                        
                        <div class="form-group">
                            <label for="contract_id"><?php echo esc_html__('Select Contract', 'wp-cddu-manager'); ?></label>
                            <select id="contract_id" name="contract_id" required>
                                <option value=""><?php echo esc_html__('-- Select Contract --', 'wp-cddu-manager'); ?></option>
                                <?php foreach ($contracts as $contract): ?>
                                    <?php
                                    $contract_data = get_post_meta($contract->ID, 'contract_data', true);
                                    $contract_data = maybe_unserialize($contract_data) ?: [];
                                    ?>
                                    <option value="<?php echo esc_attr($contract->ID); ?>">
                                        <?php echo esc_html($contract->post_title . ' - ' . ($contract_data['action'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="month"><?php echo esc_html__('Month', 'wp-cddu-manager'); ?></label>
                                <select id="month" name="month" required>
                                    <option value=""><?php echo esc_html__('-- Select Month --', 'wp-cddu-manager'); ?></option>
                                    <?php
                                    $months = [
                                        'January', 'February', 'March', 'April', 'May', 'June',
                                        'July', 'August', 'September', 'October', 'November', 'December'
                                    ];
                                    $current_month = date('F');
                                    foreach ($months as $month):
                                    ?>
                                        <option value="<?php echo esc_attr($month); ?>" <?php selected($month, $current_month); ?>>
                                            <?php echo esc_html__($month, 'wp-cddu-manager'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="year"><?php echo esc_html__('Year', 'wp-cddu-manager'); ?></label>
                                <select id="year" name="year" required>
                                    <?php
                                    $current_year = date('Y');
                                    for ($year = $current_year - 1; $year <= $current_year + 1; $year++):
                                    ?>
                                        <option value="<?php echo esc_attr($year); ?>" <?php selected($year, $current_year); ?>>
                                            <?php echo esc_html($year); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hours_worked"><?php echo esc_html__('Hours Worked', 'wp-cddu-manager'); ?></label>
                            <input type="number" id="hours_worked" name="hours_worked" step="0.5" min="0" required>
                            <small class="form-help"><?php echo esc_html__('Enter the total hours worked for this month', 'wp-cddu-manager'); ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="description"><?php echo esc_html__('Description (Optional)', 'wp-cddu-manager'); ?></label>
                            <textarea id="description" name="description" rows="4" placeholder="<?php echo esc_attr__('Describe the work performed this month...', 'wp-cddu-manager'); ?>"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?>
                            </button>
                            <a href="<?php echo home_url('/instructor-dashboard/'); ?>" class="btn btn-secondary">
                                <?php echo esc_html__('Cancel', 'wp-cddu-manager'); ?>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="submitted-timesheets">
                    <h2><?php echo esc_html__('Previous Timesheets', 'wp-cddu-manager'); ?></h2>
                    
                    <?php if (empty($timesheets)): ?>
                        <p><?php echo esc_html__('No timesheets submitted yet.', 'wp-cddu-manager'); ?></p>
                    <?php else: ?>
                        <table class="timesheets-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Month/Year', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Contract', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Hours Worked', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Status', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Submitted', 'wp-cddu-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timesheets as $timesheet): ?>
                                    <?php
                                    $month = get_post_meta($timesheet->ID, 'month', true);
                                    $year = get_post_meta($timesheet->ID, 'year', true);
                                    $hours = get_post_meta($timesheet->ID, 'hours_worked', true);
                                    $status = get_post_meta($timesheet->ID, 'status', true);
                                    $contract_id = get_post_meta($timesheet->ID, 'contract_id', true);
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($month . ' ' . $year); ?></td>
                                        <td><?php echo esc_html(get_the_title($contract_id)); ?></td>
                                        <td><?php echo esc_html($hours); ?>h</td>
                                        <td>
                                            <span class="status status-<?php echo esc_attr($status); ?>">
                                                <?php echo esc_html(ucfirst($status)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html(get_the_date('d/m/Y', $timesheet)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
