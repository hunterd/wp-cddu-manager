<?php
/**
 * Help content for enhanced learner form
 */

if (!defined('ABSPATH')) {
    exit;
}

if (isset($_GET['show_help']) && $_GET['show_help'] === 'learner_form') {
    ?>
    <div class="wrap">
        <h1><?php _e('Learner Form Help', 'wp-cddu-manager'); ?></h1>
        
        <div class="cddu-help-content">
            <h2><?php _e('Getting Started', 'wp-cddu-manager'); ?></h2>
            <p><?php _e('The enhanced learner form makes it easy to add and manage learner information. Here\'s how to use it effectively:', 'wp-cddu-manager'); ?></p>
            
            <h3><?php _e('Required Fields', 'wp-cddu-manager'); ?></h3>
            <ul>
                <li><strong><?php _e('First Name', 'wp-cddu-manager'); ?>:</strong> <?php _e('The learner\'s given name', 'wp-cddu-manager'); ?></li>
                <li><strong><?php _e('Last Name', 'wp-cddu-manager'); ?>:</strong> <?php _e('The learner\'s family name', 'wp-cddu-manager'); ?></li>
            </ul>
            
            <h3><?php _e('Tips for Best Results', 'wp-cddu-manager'); ?></h3>
            <ul>
                <li><?php _e('Fill out as much information as possible for complete records', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Use proper email format (example@domain.com)', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Enter phone numbers in the format: 01 23 45 67 89', 'wp-cddu-manager'); ?></li>
                <li><?php _e('The form auto-saves your progress as you type', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Use the Notes section for any additional relevant information', 'wp-cddu-manager'); ?></li>
            </ul>
            
            <h3><?php _e('Form Sections', 'wp-cddu-manager'); ?></h3>
            <dl>
                <dt><strong><?php _e('Personal Information', 'wp-cddu-manager'); ?></strong></dt>
                <dd><?php _e('Basic identity information including name, birth date, and social security number', 'wp-cddu-manager'); ?></dd>
                
                <dt><strong><?php _e('Address Information', 'wp-cddu-manager'); ?></strong></dt>
                <dd><?php _e('Complete mailing address including postal code and country', 'wp-cddu-manager'); ?></dd>
                
                <dt><strong><?php _e('Contact Information', 'wp-cddu-manager'); ?></strong></dt>
                <dd><?php _e('Phone numbers, email, and emergency contact details', 'wp-cddu-manager'); ?></dd>
                
                <dt><strong><?php _e('Academic Information', 'wp-cddu-manager'); ?></strong></dt>
                <dd><?php _e('Education level, enrollment status, and important dates', 'wp-cddu-manager'); ?></dd>
                
                <dt><strong><?php _e('Additional Notes', 'wp-cddu-manager'); ?></strong></dt>
                <dd><?php _e('Free text area for any other relevant information', 'wp-cddu-manager'); ?></dd>
            </dl>
            
            <h3><?php _e('Keyboard Shortcuts', 'wp-cddu-manager'); ?></h3>
            <ul>
                <li><kbd>Tab</kbd> - <?php _e('Move to next field', 'wp-cddu-manager'); ?></li>
                <li><kbd>Shift + Tab</kbd> - <?php _e('Move to previous field', 'wp-cddu-manager'); ?></li>
                <li><kbd>Enter</kbd> - <?php _e('In text fields, moves to next field', 'wp-cddu-manager'); ?></li>
            </ul>
            
            <h3><?php _e('Troubleshooting', 'wp-cddu-manager'); ?></h3>
            <p><strong><?php _e('Form won\'t save:', 'wp-cddu-manager'); ?></strong></p>
            <ul>
                <li><?php _e('Check that required fields (marked with *) are filled out', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Ensure email address is in correct format', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Verify dates are in correct format (YYYY-MM-DD)', 'wp-cddu-manager'); ?></li>
            </ul>
            
            <p><strong><?php _e('Data not appearing:', 'wp-cddu-manager'); ?></strong></p>
            <ul>
                <li><?php _e('Refresh the page to load the latest data', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Check your internet connection', 'wp-cddu-manager'); ?></li>
                <li><?php _e('Contact your administrator if problems persist', 'wp-cddu-manager'); ?></li>
            </ul>
        </div>
        
        <p>
            <a href="<?php echo admin_url('post-new.php?post_type=cddu_learner'); ?>" class="button button-primary">
                <?php _e('Add New Learner', 'wp-cddu-manager'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=cddu_learner'); ?>" class="button button-secondary">
                <?php _e('View All Learners', 'wp-cddu-manager'); ?>
            </a>
        </p>
    </div>
    
    <?php
    // Enqueue CSS for help page
    wp_enqueue_style(
        'cddu-learner-form-help',
        CDDU_MNGR_URL . 'assets/css/learner-form-help.css',
        [],
        CDDU_MNGR_VERSION
    );
    
    return;
}
?>
