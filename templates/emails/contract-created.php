<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Contract Created', 'wp-cddu-manager'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 5px; }
        .details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo esc_html__('New CDDU Contract Created', 'wp-cddu-manager'); ?></h1>
        </div>
        
        <div class="content">
            <?php if ($role === 'instructor'): ?>
                <p><?php echo esc_html__('Dear Instructor,', 'wp-cddu-manager'); ?></p>
                <p><?php echo esc_html__('A new CDDU contract has been created for you. Please review the details below:', 'wp-cddu-manager'); ?></p>
            <?php else: ?>
                <p><?php echo esc_html__('Dear Manager,', 'wp-cddu-manager'); ?></p>
                <p><?php echo esc_html__('A new CDDU contract has been created in your organization. Please review the details below:', 'wp-cddu-manager'); ?></p>
            <?php endif; ?>
            
            <div class="details">
                <h3><?php echo esc_html($contract->post_title); ?></h3>
                <p><strong><?php echo esc_html__('Action:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html($contract_data['action'] ?? ''); ?></p>
                <p><strong><?php echo esc_html__('Location:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html($contract_data['location'] ?? ''); ?></p>
                <p><strong><?php echo esc_html__('Period:', 'wp-cddu-manager'); ?></strong> 
                   <?php echo esc_html(date('d/m/Y', strtotime($contract_data['start_date'] ?? ''))); ?> - 
                   <?php echo esc_html(date('d/m/Y', strtotime($contract_data['end_date'] ?? ''))); ?>
                </p>
                <p><strong><?php echo esc_html__('Annual Hours:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html($contract_data['annual_hours'] ?? ''); ?>h</p>
                <p><strong><?php echo esc_html__('Hourly Rate:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html($contract_data['hourly_rate'] ?? ''); ?>€</p>
            </div>
            
            <?php if ($role === 'instructor'): ?>
                <p><?php echo esc_html__('You will receive a separate email with the contract document for electronic signature.', 'wp-cddu-manager'); ?></p>
                <p>
                    <a href="<?php echo home_url('/instructor-dashboard/'); ?>" class="button">
                        <?php echo esc_html__('View in Dashboard', 'wp-cddu-manager'); ?>
                    </a>
                </p>
            <?php else: ?>
                <p><?php echo esc_html__('The contract will be sent for electronic signature once finalized.', 'wp-cddu-manager'); ?></p>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=cddu_contract'); ?>" class="button">
                        <?php echo esc_html__('Manage Contracts', 'wp-cddu-manager'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p><?php echo esc_html__('This is an automated message from the CDDU Management System.', 'wp-cddu-manager'); ?></p>
            <p><?php echo esc_html(get_bloginfo('name')); ?></p>
        </div>
    </div>
</body>
</html>
