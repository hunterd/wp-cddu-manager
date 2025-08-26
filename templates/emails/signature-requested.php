<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Electronic Signature Requested', 'wp-cddu-manager'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #e67e22; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 15px 25px; background: #e67e22; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #e67e22; }
        .urgent { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo esc_html__('Electronic Signature Requested', 'wp-cddu-manager'); ?></h1>
        </div>
        
        <div class="content">
            <p><?php echo esc_html__('Dear', 'wp-cddu-manager'); ?> <?php echo esc_html($signer_data['name'] ?? ''); ?>,</p>
            
            <div class="urgent">
                <strong><?php echo esc_html__('Action Required:', 'wp-cddu-manager'); ?></strong>
                <?php echo esc_html__('Your electronic signature is required for the following document.', 'wp-cddu-manager'); ?>
            </div>
            
            <div class="details">
                <h3><?php echo esc_html(ucfirst($document_type)); ?>: <?php echo esc_html($document->post_title); ?></h3>
                <p><strong><?php echo esc_html__('Document Type:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html(ucfirst($document_type)); ?></p>
                <p><strong><?php echo esc_html__('Created Date:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html(get_the_date('d/m/Y', $document)); ?></p>
                <?php if (isset($signer_data['role'])): ?>
                    <p><strong><?php echo esc_html__('Your Role:', 'wp-cddu-manager'); ?></strong> <?php echo esc_html($signer_data['role']); ?></p>
                <?php endif; ?>
            </div>
            
            <p><?php echo esc_html__('Please click the button below to review and sign the document electronically:', 'wp-cddu-manager'); ?></p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="<?php echo esc_url($signature_url ?? '#'); ?>" class="button">
                    <?php echo esc_html__('Sign Document Now', 'wp-cddu-manager'); ?>
                </a>
            </p>
            
            <p><strong><?php echo esc_html__('Important Notes:', 'wp-cddu-manager'); ?></strong></p>
            <ul>
                <li><?php echo esc_html__('This signature link is secure and personalized for you', 'wp-cddu-manager'); ?></li>
                <li><?php echo esc_html__('You will receive a copy of the signed document by email', 'wp-cddu-manager'); ?></li>
                <li><?php echo esc_html__('If you have any questions, please contact the organization directly', 'wp-cddu-manager'); ?></li>
            </ul>
        </div>
        
        <div class="footer">
            <p><?php echo esc_html__('This is an automated message from the CDDU Management System.', 'wp-cddu-manager'); ?></p>
            <p><?php echo esc_html(get_bloginfo('name')); ?></p>
        </div>
    </div>
</body>
</html>
