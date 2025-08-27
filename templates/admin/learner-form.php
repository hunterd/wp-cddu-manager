<?php
/**
 * Enhanced Learner Form Template
 * 
 * @var array $learner_data
 * @var WP_Post $post
 */

if (!defined('ABSPATH')) {
    exit;
}

$errors = \CDDU_Manager\Admin\LearnerFormManager::get_instance()->get_validation_errors();
$is_new = !$post->ID;
?>

<div id="cddu-learner-form-container">
    <?php if (!empty($errors)): ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Please correct the following errors:', 'wp-cddu-manager'); ?></strong></p>
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="cddu-form-header">
        <h1 class="cddu-form-title">
            <?php if ($is_new): ?>
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php _e('Add New Learner', 'wp-cddu-manager'); ?>
            <?php else: ?>
                <span class="dashicons dashicons-edit-large"></span>
                <?php printf(__('Edit Learner: %s', 'wp-cddu-manager'), esc_html($post->post_title)); ?>
            <?php endif; ?>
        </h1>
        <p class="cddu-form-description">
            <?php _e('Complete the form below to register a new learner in the system. Required fields are marked with an asterisk (*).', 'wp-cddu-manager'); ?>
            <a href="<?php echo admin_url('admin.php?page=cddu-learner-help&show_help=learner_form'); ?>" target="_blank" class="cddu-help-link">
                <?php _e('Need help?', 'wp-cddu-manager'); ?>
            </a>
        </p>
    </div>

    <div class="cddu-form-content">
        <?php wp_nonce_field('cddu_learner_form_nonce', 'cddu_learner_form_nonce'); ?>
        
        <!-- Personal Information Section -->
        <div class="cddu-form-section">
            <h2 class="cddu-section-title">
                <span class="dashicons dashicons-admin-users"></span>
                <?php _e('Personal Information', 'wp-cddu-manager'); ?>
            </h2>
            
            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field <?php echo isset($errors['first_name']) ? 'has-error' : ''; ?>">
                    <label for="first_name" class="cddu-field-label">
                        <?php _e('First Name', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           value="<?php echo esc_attr($learner_data['first_name']); ?>" 
                           class="cddu-field-input" 
                           required
                           autocomplete="given-name">
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['first_name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="cddu-form-field <?php echo isset($errors['last_name']) ? 'has-error' : ''; ?>">
                    <label for="last_name" class="cddu-field-label">
                        <?php _e('Last Name', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           value="<?php echo esc_attr($learner_data['last_name']); ?>" 
                           class="cddu-field-input" 
                           required
                           autocomplete="family-name">
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['last_name']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field <?php echo isset($errors['birth_date']) ? 'has-error' : ''; ?>">
                    <label for="birth_date" class="cddu-field-label">
                        <?php _e('Birth Date', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="date" 
                           id="birth_date" 
                           name="birth_date" 
                           value="<?php echo esc_attr($learner_data['birth_date']); ?>" 
                           class="cddu-field-input"
                           autocomplete="bday">
                    <?php if (isset($errors['birth_date'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['birth_date']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="cddu-form-field">
                    <label for="social_security" class="cddu-field-label">
                        <?php _e('Social Security Number', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="social_security" 
                           name="social_security" 
                           value="<?php echo esc_attr($learner_data['social_security']); ?>" 
                           class="cddu-field-input"
                           placeholder="<?php _e('e.g., 1 23 45 67 890 123 45', 'wp-cddu-manager'); ?>">
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div class="cddu-form-section">
            <h2 class="cddu-section-title">
                <span class="dashicons dashicons-location-alt"></span>
                <?php _e('Address Information', 'wp-cddu-manager'); ?>
            </h2>
            
            <div class="cddu-form-field">
                <label for="address" class="cddu-field-label">
                    <?php _e('Street Address', 'wp-cddu-manager'); ?>
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="3" 
                          class="cddu-field-textarea"
                          autocomplete="street-address"><?php echo esc_textarea($learner_data['address']); ?></textarea>
            </div>

            <div class="cddu-form-grid cddu-grid-3">
                <div class="cddu-form-field">
                    <label for="postal_code" class="cddu-field-label">
                        <?php _e('Postal Code', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="postal_code" 
                           name="postal_code" 
                           value="<?php echo esc_attr($learner_data['postal_code']); ?>" 
                           class="cddu-field-input"
                           autocomplete="postal-code">
                </div>

                <div class="cddu-form-field">
                    <label for="city" class="cddu-field-label">
                        <?php _e('City', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="city" 
                           name="city" 
                           value="<?php echo esc_attr($learner_data['city']); ?>" 
                           class="cddu-field-input"
                           autocomplete="address-level2">
                </div>

                <div class="cddu-form-field">
                    <label for="country" class="cddu-field-label">
                        <?php _e('Country', 'wp-cddu-manager'); ?>
                    </label>
                    <select id="country" name="country" class="cddu-field-select" autocomplete="country-name">
                        <option value="France" <?php selected($learner_data['country'], 'France'); ?>><?php _e('France', 'wp-cddu-manager'); ?></option>
                        <option value="Belgium" <?php selected($learner_data['country'], 'Belgium'); ?>><?php _e('Belgium', 'wp-cddu-manager'); ?></option>
                        <option value="Switzerland" <?php selected($learner_data['country'], 'Switzerland'); ?>><?php _e('Switzerland', 'wp-cddu-manager'); ?></option>
                        <option value="Canada" <?php selected($learner_data['country'], 'Canada'); ?>><?php _e('Canada', 'wp-cddu-manager'); ?></option>
                        <option value="Other" <?php selected($learner_data['country'], 'Other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="cddu-form-section">
            <h2 class="cddu-section-title">
                <span class="dashicons dashicons-email-alt"></span>
                <?php _e('Contact Information', 'wp-cddu-manager'); ?>
            </h2>
            
            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                    <label for="email" class="cddu-field-label">
                        <?php _e('Email Address', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo esc_attr($learner_data['email']); ?>" 
                           class="cddu-field-input"
                           autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="cddu-form-field">
                    <label for="phone" class="cddu-field-label">
                        <?php _e('Phone Number', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo esc_attr($learner_data['phone']); ?>" 
                           class="cddu-field-input"
                           autocomplete="tel">
                </div>
            </div>

            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field">
                    <label for="mobile_phone" class="cddu-field-label">
                        <?php _e('Mobile Phone', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="tel" 
                           id="mobile_phone" 
                           name="mobile_phone" 
                           value="<?php echo esc_attr($learner_data['mobile_phone']); ?>" 
                           class="cddu-field-input"
                           autocomplete="tel">
                </div>

                <div class="cddu-form-field">
                    <label for="emergency_contact" class="cddu-field-label">
                        <?php _e('Emergency Contact Name', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="text" 
                           id="emergency_contact" 
                           name="emergency_contact" 
                           value="<?php echo esc_attr($learner_data['emergency_contact']); ?>" 
                           class="cddu-field-input">
                </div>
            </div>

            <div class="cddu-form-field">
                <label for="emergency_phone" class="cddu-field-label">
                    <?php _e('Emergency Contact Phone', 'wp-cddu-manager'); ?>
                </label>
                <input type="tel" 
                       id="emergency_phone" 
                       name="emergency_phone" 
                       value="<?php echo esc_attr($learner_data['emergency_phone']); ?>" 
                       class="cddu-field-input cddu-field-half"
                       autocomplete="tel">
            </div>
        </div>

        <!-- Academic Information Section -->
        <div class="cddu-form-section">
            <h2 class="cddu-section-title">
                <span class="dashicons dashicons-welcome-learn-more"></span>
                <?php _e('Academic Information', 'wp-cddu-manager'); ?>
            </h2>
            
            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field">
                    <label for="level" class="cddu-field-label">
                        <?php _e('Education Level', 'wp-cddu-manager'); ?>
                    </label>
                    <select id="level" name="level" class="cddu-field-select">
                        <option value=""><?php _e('Select education level', 'wp-cddu-manager'); ?></option>
                        <option value="bac_minus" <?php selected($learner_data['level'], 'bac_minus'); ?>><?php _e('Below Baccalaureate', 'wp-cddu-manager'); ?></option>
                        <option value="bac" <?php selected($learner_data['level'], 'bac'); ?>><?php _e('Baccalaureate', 'wp-cddu-manager'); ?></option>
                        <option value="bac_plus_2" <?php selected($learner_data['level'], 'bac_plus_2'); ?>><?php _e('Bac +2', 'wp-cddu-manager'); ?></option>
                        <option value="bac_plus_3" <?php selected($learner_data['level'], 'bac_plus_3'); ?>><?php _e('Bac +3', 'wp-cddu-manager'); ?></option>
                        <option value="bac_plus_5" <?php selected($learner_data['level'], 'bac_plus_5'); ?>><?php _e('Bac +5', 'wp-cddu-manager'); ?></option>
                        <option value="other" <?php selected($learner_data['level'], 'other'); ?>><?php _e('Other', 'wp-cddu-manager'); ?></option>
                    </select>
                </div>

                <div class="cddu-form-field">
                    <label for="status" class="cddu-field-label">
                        <?php _e('Status', 'wp-cddu-manager'); ?>
                    </label>
                    <select id="status" name="status" class="cddu-field-select">
                        <option value="active" <?php selected($learner_data['status'], 'active'); ?>><?php _e('Active', 'wp-cddu-manager'); ?></option>
                        <option value="inactive" <?php selected($learner_data['status'], 'inactive'); ?>><?php _e('Inactive', 'wp-cddu-manager'); ?></option>
                        <option value="completed" <?php selected($learner_data['status'], 'completed'); ?>><?php _e('Completed', 'wp-cddu-manager'); ?></option>
                        <option value="dropped" <?php selected($learner_data['status'], 'dropped'); ?>><?php _e('Dropped', 'wp-cddu-manager'); ?></option>
                    </select>
                </div>
            </div>

            <div class="cddu-form-grid cddu-grid-2">
                <div class="cddu-form-field <?php echo isset($errors['enrollment_date']) ? 'has-error' : ''; ?>">
                    <label for="enrollment_date" class="cddu-field-label">
                        <?php _e('Enrollment Date', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="date" 
                           id="enrollment_date" 
                           name="enrollment_date" 
                           value="<?php echo esc_attr($learner_data['enrollment_date']); ?>" 
                           class="cddu-field-input">
                    <?php if (isset($errors['enrollment_date'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['enrollment_date']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="cddu-form-field <?php echo isset($errors['expected_completion_date']) ? 'has-error' : ''; ?>">
                    <label for="expected_completion_date" class="cddu-field-label">
                        <?php _e('Expected Completion Date', 'wp-cddu-manager'); ?>
                    </label>
                    <input type="date" 
                           id="expected_completion_date" 
                           name="expected_completion_date" 
                           value="<?php echo esc_attr($learner_data['expected_completion_date']); ?>" 
                           class="cddu-field-input">
                    <?php if (isset($errors['expected_completion_date'])): ?>
                        <span class="cddu-field-error"><?php echo esc_html($errors['expected_completion_date']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Additional Notes Section -->
        <div class="cddu-form-section">
            <h2 class="cddu-section-title">
                <span class="dashicons dashicons-edit-large"></span>
                <?php _e('Additional Notes', 'wp-cddu-manager'); ?>
            </h2>
            
            <div class="cddu-form-field">
                <label for="notes" class="cddu-field-label">
                    <?php _e('Notes', 'wp-cddu-manager'); ?>
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="5" 
                          class="cddu-field-textarea"
                          placeholder="<?php _e('Add any additional notes about the learner...', 'wp-cddu-manager'); ?>"><?php echo esc_textarea($learner_data['notes']); ?></textarea>
                <p class="cddu-field-description">
                    <?php _e('Any additional information about the learner that might be relevant for their education or support.', 'wp-cddu-manager'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Set the enrollment date to today if it's a new learner and the field is empty
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentDate = document.getElementById('enrollment_date');
    if (enrollmentDate && !enrollmentDate.value && <?php echo $is_new ? 'true' : 'false'; ?>) {
        const today = new Date().toISOString().split('T')[0];
        enrollmentDate.value = today;
    }
});
</script>
