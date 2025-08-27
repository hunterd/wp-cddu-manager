/**
 * Enhanced Learner Form JavaScript
 */

(function($) {
    'use strict';

    class LearnerForm {
        constructor() {
            this.form = $('#post');
            this.container = $('#cddu-learner-form-container');
            this.strings = cdduLearnerForm.strings || {};
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.setupAutoSave();
            this.initializeFields();
        }

        bindEvents() {
            // Form validation on field blur
            this.container.on('blur', 'input, select, textarea', (e) => {
                this.validateField($(e.target));
            });

            // Real-time validation for required fields
            this.container.on('input', 'input[required]', (e) => {
                this.clearFieldError($(e.target));
            });

            // Auto-format phone numbers
            this.container.on('input', 'input[type="tel"]', (e) => {
                this.formatPhoneNumber($(e.target));
            });

            // Auto-format social security number
            this.container.on('input', '#social_security', (e) => {
                this.formatSocialSecurity($(e.target));
            });

            // Prevent form submission on Enter key in text fields
            this.container.on('keydown', 'input[type="text"], input[type="email"], input[type="tel"]', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.focusNextField($(e.target));
                }
            });

            // Handle form submission with validation (only for Publish button)
            this.form.on('submit', (e) => {
                const $submitButton = $(document.activeElement);
                
                // Only validate for publish button, allow draft saves without validation
                if ($submitButton.is('#publish') && !this.validateForm()) {
                    e.preventDefault();
                    this.showNotification(this.strings.validationErrors, 'error');
                    return false;
                }
                
                // Allow form to submit normally
                return true;
            });
        }

        setupAutoSave() {
            // Auto-save is disabled when using native form submission
            // Users will save explicitly using the form buttons
            return;
        }

        initializeFields() {
            // Set focus on first field for new learners
            if (window.location.href.includes('post-new.php')) {
                setTimeout(() => {
                    $('#first_name').focus();
                }, 100);
            }

            // Initialize country field if empty
            const $country = $('#country');
            if (!$country.val()) {
                $country.val('France');
            }

            // Highlight fields with server-side validation errors
            this.highlightServerErrors();
        }

        highlightServerErrors() {
            // Look for server-side error messages and highlight corresponding fields
            const $errorNotice = $('.notice-error');
            if ($errorNotice.length) {
                // Scroll to the error notice
                $errorNotice[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Try to find and highlight fields mentioned in error messages
                $errorNotice.find('li').each((index, element) => {
                    const errorText = $(element).text().toLowerCase();
                    
                    // Map common error patterns to field names
                    const fieldMappings = {
                        'first name': 'first_name',
                        'last name': 'last_name', 
                        'email': 'email',
                        'birth date': 'birth_date',
                        'enrollment date': 'enrollment_date',
                        'completion date': 'expected_completion_date'
                    };
                    
                    // Find matching field and highlight it
                    Object.keys(fieldMappings).forEach(pattern => {
                        if (errorText.includes(pattern)) {
                            const $field = $(`[name="${fieldMappings[pattern]}"]`);
                            if ($field.length) {
                                this.showFieldError($field, $(element).text());
                                // Focus on the first error field
                                if (index === 0) {
                                    setTimeout(() => {
                                        $field.focus();
                                    }, 500);
                                }
                            }
                        }
                    });
                });
            }
        }

        validateField($field) {
            const fieldName = $field.attr('name');
            const value = $field.val().trim();
            const $fieldContainer = $field.closest('.cddu-form-field');
            
            this.clearFieldError($field);

            // Required field validation
            if ($field.prop('required') && !value) {
                this.showFieldError($field, this.strings.requiredField);
                return false;
            }

            // Email validation
            if ($field.attr('type') === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    this.showFieldError($field, this.strings.invalidEmail);
                    return false;
                }
            }

            // Phone validation
            if ($field.attr('type') === 'tel' && value) {
                const phoneRegex = /^[\d\s\-\+\(\)\.]+$/;
                if (!phoneRegex.test(value)) {
                    this.showFieldError($field, this.strings.invalidPhone);
                    return false;
                }
            }

            // Date validation
            if ($field.attr('type') === 'date' && value) {
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    this.showFieldError($field, 'Please enter a valid date.');
                    return false;
                }
                
                // Check if birth date is not in the future
                if (fieldName === 'birth_date' && date > new Date()) {
                    this.showFieldError($field, 'Birth date cannot be in the future.');
                    return false;
                }
            }

            return true;
        }

        showFieldError($field, message) {
            const $fieldContainer = $field.closest('.cddu-form-field');
            $fieldContainer.addClass('has-error');
            
            let $errorSpan = $fieldContainer.find('.cddu-field-error');
            if (!$errorSpan.length) {
                $errorSpan = $('<span class="cddu-field-error"></span>');
                $fieldContainer.append($errorSpan);
            }
            
            $errorSpan.text(message);
        }

        clearFieldError($field) {
            const $fieldContainer = $field.closest('.cddu-form-field');
            $fieldContainer.removeClass('has-error');
            $fieldContainer.find('.cddu-field-error').remove();
        }

        formatPhoneNumber($field) {
            let value = $field.val().replace(/\D/g, '');
            
            // French phone number format
            if (value.length >= 10) {
                value = value.substring(0, 10);
                value = value.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');
            }
            
            $field.val(value);
        }

        formatSocialSecurity($field) {
            let value = $field.val().replace(/\D/g, '');
            
            // French social security format: 1 23 45 67 890 123 45
            if (value.length >= 13) {
                value = value.substring(0, 15);
                value = value.replace(/(\d{1})(\d{2})(\d{2})(\d{2})(\d{3})(\d{3})(\d{2})/, '$1 $2 $3 $4 $5 $6 $7');
            }
            
            $field.val(value);
        }

        focusNextField($currentField) {
            const $fields = this.container.find('input, select, textarea').filter(':visible');
            const currentIndex = $fields.index($currentField);
            const $nextField = $fields.eq(currentIndex + 1);
            
            if ($nextField.length) {
                $nextField.focus();
            }
        }

        validateForm() {
            let isValid = true;
            
            // Clear previous errors
            this.container.find('.cddu-form-field').removeClass('has-error');
            this.container.find('.cddu-field-error').remove();
            
            // Validate all fields
            this.container.find('input, select, textarea').each((index, element) => {
                if (!this.validateField($(element))) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        saveLearner(status = 'publish') {
            // This method is now deprecated - we use native form submission
            console.warn('saveLearner method is deprecated - using native form submission');
        }

        setLoadingState(isLoading, status) {
            // This method is now deprecated - using native form submission
            console.warn('setLoadingState method is deprecated - using native form submission');
        }

        handleValidationErrors(data) {
            if (data.errors) {
                // Clear existing errors
                this.container.find('.cddu-form-field').removeClass('has-error');
                this.container.find('.cddu-field-error').remove();
                
                // Show field-specific errors
                Object.keys(data.errors).forEach(fieldName => {
                    const $field = this.container.find(`[name="${fieldName}"]`);
                    if ($field.length) {
                        this.showFieldError($field, data.errors[fieldName]);
                    }
                });
                
                // Show general error message
                this.showNotification(data.message || this.strings.validationErrors, 'error');
                
                // Focus on first error field
                const $firstError = this.container.find('.has-error').first().find('input, select, textarea');
                if ($firstError.length) {
                    $firstError.focus();
                }
            } else {
                this.showNotification(data.message || 'An error occurred', 'error');
            }
        }

        autoSave() {
            // Auto-save is disabled since we now use native form submission
            // No notification will be shown to avoid false positives
            // The user will get feedback only on actual form submission
            return;
        }

        getFormData() {
            const data = {};
            
            this.container.find('input, select, textarea').each((index, element) => {
                const $field = $(element);
                const name = $field.attr('name');
                
                if (name && name !== 'cddu_learner_form_nonce') {
                    data[name] = $field.val();
                }
            });
            
            return data;
        }

        showNotification(message, type = 'info') {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            this.container.prepend($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(() => {
                    $notice.remove();
                });
            }, 5000);
        }

        showTempNotification(message, type = 'info') {
            const $notification = $(`
                <div class="cddu-temp-notification cddu-temp-${type}">
                    ${message}
                </div>
            `);
            
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 2000);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('#cddu-learner-form-container').length) {
            new LearnerForm();
        }
    });

    // Add temp notification styles
    $('head').append(`
        <style>
            .cddu-temp-notification {
                position: fixed;
                top: 32px;
                right: 20px;
                background: #fff;
                color: #23282d;
                padding: 12px 20px;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                z-index: 999999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                border-left: 4px solid #00a32a;
            }
            
            .cddu-temp-notification.show {
                transform: translateX(0);
            }
            
            .cddu-temp-error {
                border-left-color: #d63638;
            }
            
            .cddu-temp-success {
                border-left-color: #00a32a;
            }
            
            .cddu-loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.7);
                z-index: 999;
                pointer-events: none;
            }
            
            .cddu-spin {
                animation: cddu-spin 1s linear infinite;
            }
            
            @keyframes cddu-spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            button.loading {
                position: relative;
                pointer-events: none;
                opacity: 0.7;
            }
            
            #cddu-learner-form-container {
                position: relative;
            }
            
            .cddu-form-field.has-error {
                border-left: 3px solid #d63638;
                background-color: #fef7f7;
                padding-left: 12px;
                margin-left: -15px;
            }
            
            .cddu-field-error {
                color: #d63638;
                font-size: 13px;
                margin-top: 5px;
                display: block;
            }
        </style>
    `);

})(jQuery);
