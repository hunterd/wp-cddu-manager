<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html__('Instructor Dashboard', 'wp-cddu-manager'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="cddu-instructor-dashboard">
    <div class="dashboard-header">
        <div class="container">
            <h1><?php echo esc_html__('Instructor Dashboard', 'wp-cddu-manager'); ?></h1>
            <div class="user-info">
                <?php echo esc_html__('Welcome', 'wp-cddu-manager'); ?>, <strong><?php echo esc_html($current_user->display_name); ?></strong>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="logout-link"><?php echo esc_html__('Logout', 'wp-cddu-manager'); ?></a>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-nav">
                <a href="<?php echo home_url('/instructor-dashboard/'); ?>" class="nav-link active">
                    <?php echo esc_html__('Dashboard', 'wp-cddu-manager'); ?>
                </a>
                <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="nav-link">
                    <?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?>
                </a>
            </div>

            <div class="dashboard-main">
                <div class="instructor-info">
                    <h2><?php echo esc_html__('Your Profile', 'wp-cddu-manager'); ?></h2>
                    <?php
                    // Get instructor data from user meta
                    $instructor_data = get_user_meta($current_user->ID, 'instructor_data', true);
                    $instructor_data = maybe_unserialize($instructor_data) ?: [];
                    ?>
                    <p><strong><?php echo esc_html__('Name', 'wp-cddu-manager'); ?>:</strong> 
                        <?php echo esc_html($current_user->display_name); ?>
                    </p>
                    <p><strong><?php echo esc_html__('Email', 'wp-cddu-manager'); ?>:</strong> 
                        <?php echo esc_html($current_user->user_email); ?>
                    </p>
                    <?php if (!empty($instructor_data['address'])): ?>
                    <p><strong><?php echo esc_html__('Address', 'wp-cddu-manager'); ?>:</strong> 
                        <?php echo esc_html($instructor_data['address']); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="contracts-section">
                    <h2><?php echo esc_html__('Your Contracts', 'wp-cddu-manager'); ?></h2>
                    
                    <?php if (empty($contracts)): ?>
                        <p><?php echo esc_html__('No contracts found.', 'wp-cddu-manager'); ?></p>
                    <?php else: ?>
                        <div class="contracts-grid">
                            <?php foreach ($contracts as $contract): ?>
                                <?php
                                $contract_data = get_post_meta($contract->ID, 'contract_data', true);
                                $contract_data = maybe_unserialize($contract_data) ?: [];
                                $status = get_post_meta($contract->ID, 'status', true) ?: 'draft';
                                $pdf_url = get_post_meta($contract->ID, 'generated_pdf_url', true);
                                ?>
                                <div class="contract-card">
                                    <div class="contract-header">
                                        <h3><?php echo esc_html($contract->post_title); ?></h3>
                                        <span class="status status-<?php echo esc_attr($status); ?>">
                                            <?php echo esc_html(ucfirst($status)); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="contract-details">
                                        <p><strong><?php echo esc_html__('Action', 'wp-cddu-manager'); ?>:</strong> 
                                            <?php echo esc_html($contract_data['action'] ?? ''); ?>
                                        </p>
                                        <p><strong><?php echo esc_html__('Period', 'wp-cddu-manager'); ?>:</strong> 
                                            <?php echo esc_html($contract_data['start_date'] ?? ''); ?> - <?php echo esc_html($contract_data['end_date'] ?? ''); ?>
                                        </p>
                                        <p><strong><?php echo esc_html__('Annual Hours', 'wp-cddu-manager'); ?>:</strong> 
                                            <?php echo esc_html($contract_data['annual_hours'] ?? ''); ?>h
                                        </p>
                                        <p><strong><?php echo esc_html__('Hourly Rate', 'wp-cddu-manager'); ?>:</strong> 
                                            <?php echo esc_html($contract_data['hourly_rate'] ?? ''); ?>€
                                        </p>
                                    </div>
                                    
                                    <div class="contract-actions">
                                        <a href="<?php echo home_url('/instructor-dashboard/contract/' . $contract->ID . '/'); ?>" 
                                           class="btn btn-primary">
                                            <?php echo esc_html__('View Details', 'wp-cddu-manager'); ?>
                                        </a>
                                        
                                        <?php if ($pdf_url): ?>
                                            <a href="<?php echo esc_url($pdf_url); ?>" 
                                               class="btn btn-secondary" target="_blank">
                                                <?php echo esc_html__('Download PDF', 'wp-cddu-manager'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="recent-timesheets">
                    <h2><?php echo esc_html__('Recent Timesheets', 'wp-cddu-manager'); ?></h2>
                    
                    <?php
                    $recent_timesheets = get_posts([
                        'post_type' => 'cddu_timesheet',
                        'meta_query' => [
                            [
                                'key' => 'instructor_user_id',
                                'value' => $current_user->ID,
                                'compare' => '='
                            ]
                        ],
                        'numberposts' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ]);
                    ?>
                    
                    <?php if (empty($recent_timesheets)): ?>
                        <p><?php echo esc_html__('No timesheets submitted yet.', 'wp-cddu-manager'); ?></p>
                        <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="btn btn-primary">
                            <?php echo esc_html__('Submit Your First Timesheet', 'wp-cddu-manager'); ?>
                        </a>
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
                                <?php foreach ($recent_timesheets as $timesheet): ?>
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
                        
                        <p>
                            <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="btn btn-primary">
                                <?php echo esc_html__('Submit New Timesheet', 'wp-cddu-manager'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
