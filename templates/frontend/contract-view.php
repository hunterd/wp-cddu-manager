<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html__('Contract Details', 'wp-cddu-manager'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="cddu-instructor-dashboard">
    <div class="dashboard-header">
        <div class="container">
            <h1><?php echo esc_html__('Contract Details', 'wp-cddu-manager'); ?></h1>
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
                <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="nav-link">
                    <?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?>
                </a>
            </div>

            <div class="dashboard-main">
                <div class="contract-details-section">
                    <div class="contract-header">
                        <h2><?php echo esc_html($contract->post_title); ?></h2>
                        <span class="status status-<?php echo esc_attr(get_post_meta($contract_id, 'status', true) ?: 'draft'); ?>">
                            <?php echo esc_html(ucfirst(get_post_meta($contract_id, 'status', true) ?: 'draft')); ?>
                        </span>
                    </div>

                    <?php if ($contract_data): ?>
                        <div class="contract-info">
                            <div class="info-section">
                                <h3><?php echo esc_html__('Mission Information', 'wp-cddu-manager'); ?></h3>
                                <table class="info-table">
                                    <tr>
                                        <th><?php echo esc_html__('Training Action', 'wp-cddu-manager'); ?></th>
                                        <td><?php echo esc_html($contract_data['action'] ?? ''); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo esc_html__('Location', 'wp-cddu-manager'); ?></th>
                                        <td><?php echo esc_html($contract_data['location'] ?? ''); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo esc_html__('Period', 'wp-cddu-manager'); ?></th>
                                        <td>
                                            <?php 
                                            if (!empty($contract_data['start_date']) && !empty($contract_data['end_date'])) {
                                                $start = new DateTime($contract_data['start_date']);
                                                $end = new DateTime($contract_data['end_date']);
                                                echo esc_html($start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'));
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo esc_html__('Annual Hours (H_a)', 'wp-cddu-manager'); ?></th>
                                        <td><?php echo esc_html($contract_data['annual_hours'] ?? ''); ?>h</td>
                                    </tr>
                                    <tr>
                                        <th><?php echo esc_html__('Hourly Rate', 'wp-cddu-manager'); ?></th>
                                        <td><?php echo esc_html($contract_data['hourly_rate'] ?? ''); ?>€</td>
                                    </tr>
                                </table>
                            </div>

                            <?php if ($calculations): ?>
                                <div class="info-section">
                                    <h3><?php echo esc_html__('Calculations', 'wp-cddu-manager'); ?></h3>
                                    <table class="info-table">
                                        <tr>
                                            <th><?php echo esc_html__('Number of weeks', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html($calculations['nb_weeks'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Weekly intensity', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['intensity'] ?? 0, 2)); ?>h/week</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Daily intensity', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['daily_intensity'] ?? 0, 2)); ?>h/day</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Working days needed', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['working_days'] ?? 0, 1)); ?> days</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Daily working hours (Org)', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['daily_working_hours'] ?? 7, 1)); ?>h/day</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Preparation hours (H_p)', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['hp'] ?? 0, 2)); ?>h</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Total hours (H_t)', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['ht'] ?? 0, 2)); ?>h</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="info-section">
                                    <h3><?php echo esc_html__('Remuneration', 'wp-cddu-manager'); ?></h3>
                                    <table class="info-table">
                                        <tr>
                                            <th><?php echo esc_html__('Gross amount', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['gross'] ?? 0, 2)); ?>€</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Usage bonus (6%)', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['bonus'] ?? 0, 2)); ?>€</td>
                                        </tr>
                                        <tr>
                                            <th><?php echo esc_html__('Paid leave (12%)', 'wp-cddu-manager'); ?></th>
                                            <td><?php echo esc_html(number_format($calculations['paid_leave'] ?? 0, 2)); ?>€</td>
                                        </tr>
                                        <tr class="total-row">
                                            <th><?php echo esc_html__('Total payable', 'wp-cddu-manager'); ?></th>
                                            <td><strong><?php echo esc_html(number_format($calculations['total'] ?? 0, 2)); ?>€</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="contract-actions">
                        <?php
                        $pdf_url = get_post_meta($contract_id, 'generated_pdf_url', true);
                        if ($pdf_url):
                        ?>
                            <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-primary" target="_blank">
                                <?php echo esc_html__('Download Contract PDF', 'wp-cddu-manager'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo home_url('/instructor-dashboard/timesheet/'); ?>" class="btn btn-secondary">
                            <?php echo esc_html__('Submit Timesheet', 'wp-cddu-manager'); ?>
                        </a>
                        
                        <a href="<?php echo home_url('/instructor-dashboard/'); ?>" class="btn btn-outline">
                            <?php echo esc_html__('Back to Dashboard', 'wp-cddu-manager'); ?>
                        </a>
                    </div>
                </div>

                <div class="contract-timesheets">
                    <h3><?php echo esc_html__('Timesheets for this contract', 'wp-cddu-manager'); ?></h3>
                    
                    <?php
                    $contract_timesheets = get_posts([
                        'post_type' => 'cddu_timesheet',
                        'meta_query' => [
                            [
                                'key' => 'contract_id',
                                'value' => $contract_id,
                                'compare' => '='
                            ]
                        ],
                        'numberposts' => -1,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ]);
                    ?>
                    
                    <?php if (empty($contract_timesheets)): ?>
                        <p><?php echo esc_html__('No timesheets submitted for this contract yet.', 'wp-cddu-manager'); ?></p>
                    <?php else: ?>
                        <table class="timesheets-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Month/Year', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Hours Worked', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Status', 'wp-cddu-manager'); ?></th>
                                    <th><?php echo esc_html__('Submitted', 'wp-cddu-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contract_timesheets as $timesheet): ?>
                                    <?php
                                    $month = get_post_meta($timesheet->ID, 'month', true);
                                    $year = get_post_meta($timesheet->ID, 'year', true);
                                    $hours = get_post_meta($timesheet->ID, 'hours_worked', true);
                                    $status = get_post_meta($timesheet->ID, 'status', true);
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($month . ' ' . $year); ?></td>
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
