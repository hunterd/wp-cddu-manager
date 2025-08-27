jQuery(document).ready(function($) {
    // Utility functions for translations
    function __(text) {
        return text; // In a real implementation, this would handle translations
    }
    
    // Auto-fill mission data when mission is selected
    $('#mission_id').on('change', function() {
        const missionId = $(this).val();
        if (!missionId) return;
        
        $.post(ajaxurl, {
            action: 'cddu_get_mission_data',
            mission_id: missionId,
            nonce: cddu_ajax.nonce
        }, function(response) {
            if (response.success) {
                const data = response.data;
                $('#action').val(data.action || '');
                $('#location').val(data.location || '');
                $('#annual_hours').val(data.annual_hours || '');
                $('#hourly_rate').val(data.hourly_rate || '');
                $('#start_date').val(data.start_date || '');
                $('#end_date').val(data.end_date || '');
            }
        });
    });
    
    // Calculate values
    $('#calculate-btn').on('click', function() {
        const formData = {
            action: 'cddu_calculate_values',
            annual_hours: $('#annual_hours').val(),
            hourly_rate: $('#hourly_rate').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            organization_id: $('#organization_id').val(),
            nonce: cddu_ajax.nonce
        };
        
        if (!formData.annual_hours || !formData.hourly_rate || !formData.start_date || !formData.end_date) {
            CDDUNotifications.show(
                __('Please fill in all required fields (Annual hours, Hourly rate, Start date, End date)', 'wp-cddu-manager'),
                'error',
                'contract-notifications'
            );
            return;
        }
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                const calc = response.data;
                $('#calc-nb-weeks').text(calc.nb_weeks);
                $('#calc-intensity').text(calc.intensity_formatted || (calc.intensity + ' h/week'));
                $('#calc-hp').text(calc.hp_formatted || (calc.hp + ' h'));
                $('#calc-ht').text(calc.ht_formatted || (calc.ht + ' h'));
                $('#calc-daily-intensity').text(calc.daily_intensity_formatted || (calc.daily_intensity + ' h/day'));
                $('#calc-working-days').text(calc.working_days_formatted || (calc.working_days + ' days'));
                $('#calc-daily-working-hours').text(calc.daily_working_hours_formatted || (calc.daily_working_hours + ' h/day'));
                $('#calc-gross').text(calc.gross_formatted || (calc.gross + ' €'));
                $('#calc-bonus').text(calc.bonus_formatted || (calc.bonus + ' €'));
                $('#calc-paid-leave').text(calc.paid_leave_formatted || (calc.paid_leave + ' €'));
                $('#calc-total').text(calc.total_formatted || (calc.total + ' €'));
                
                $('#calculation-results').show();
            } else {
                CDDUNotifications.show(
                    __('Error:', 'wp-cddu-manager') + ' ' + (response.data.message || __('Calculation failed', 'wp-cddu-manager')),
                    'error',
                    'contract-notifications'
                );
            }
        });
    });
    
    // Generate contract
    $('#generate-contract-btn').on('click', function() {
        // Get editor content
        let contractContent = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('contract_content')) {
            contractContent = tinyMCE.get('contract_content').getContent();
        } else {
            contractContent = $('#contract_content').val();
        }
        
        const formData = {
            action: 'cddu_generate_contract',
            organization_id: $('#organization_id').val(),
            instructor_user_id: $('#instructor_user_id').val(),
            mission_id: $('#mission_id').val(),
            action: $('#action').val(),
            location: $('#location').val(),
            annual_hours: $('#annual_hours').val(),
            hourly_rate: $('#hourly_rate').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            contract_content: contractContent,
            nonce: cddu_ajax.nonce
        };
        
        if (!formData.organization_id || !formData.instructor_user_id) {
            CDDUNotifications.show(
                __('Please select an organization and instructor', 'wp-cddu-manager'),
                'error',
                'contract-notifications'
            );
            return;
        }
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                CDDUNotifications.show(
                    response.data.message,
                    'success',
                    'contract-notifications'
                );
                if (confirm(__('Contract created successfully! Would you like to edit it now?', 'wp-cddu-manager'))) {
                    window.location.href = response.data.edit_url;
                }
            } else {
                CDDUNotifications.show(
                    __('Error:', 'wp-cddu-manager') + ' ' + (response.data.message || __('Contract generation failed', 'wp-cddu-manager')),
                    'error',
                    'contract-notifications'
                );
            }
        });
    });
    
    // Preview contract (placeholder)
    $('#preview-contract-btn').on('click', function() {
        // Get editor content
        let contractContent = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('contract_content')) {
            contractContent = tinyMCE.get('contract_content').getContent();
        } else {
            contractContent = $('#contract_content').val();
        }
        
        const formData = {
            action: 'cddu_preview_contract',
            organization_id: $('#organization_id').val(),
            instructor_user_id: $('#instructor_user_id').val(),
            action: $('#action').val(),
            location: $('#location').val(),
            annual_hours: $('#annual_hours').val(),
            hourly_rate: $('#hourly_rate').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            contract_content: contractContent,
            nonce: cddu_ajax.nonce
        };
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                // Open preview in new window
                const previewWindow = window.open('', '_blank');
                previewWindow.document.write(response.data.html);
                previewWindow.document.close();
            } else {
                CDDUNotifications.show(
                    __('Error:', 'wp-cddu-manager') + ' ' + (response.data.message || __('Preview failed', 'wp-cddu-manager')),
                    'error',
                    'contract-notifications'
                );
            }
        });
    });
    
    // Variable insertion helper
    $('.variable-group li').on('click', function() {
        const variable = $(this).find('code').text();
        if (!variable) return;
        
        // Insert variable into TinyMCE editor
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('contract_content')) {
            tinyMCE.get('contract_content').execCommand('mceInsertContent', false, variable);
        } else {
            // Fallback for text mode
            const textarea = document.getElementById('contract_content');
            if (textarea) {
                const cursorPos = textarea.selectionStart;
                const textBefore = textarea.value.substring(0, cursorPos);
                const textAfter = textarea.value.substring(cursorPos);
                textarea.value = textBefore + variable + textAfter;
                textarea.selectionStart = textarea.selectionEnd = cursorPos + variable.length;
                textarea.focus();
            }
        }
    });
    
    // Add tooltip to variable items
    $('.variable-group li').attr('title', __('Click to insert this variable into the editor', 'wp-cddu-manager'));
    
    // Template management
    function loadTemplates() {
        $.post(cddu_ajax.ajax_url, {
            action: 'cddu_get_templates',
            nonce: cddu_ajax.nonce
        }, function(response) {
            if (response.success) {
                const selector = $('#template-selector');
                selector.empty().append('<option value="">' + __('-- Select Template --', 'wp-cddu-manager') + '</option>');
                
                Object.keys(response.data.templates).forEach(function(templateName) {
                    selector.append('<option value="' + templateName + '">' + templateName + '</option>');
                });
            }
        });
    }
    
    // Load templates on page load
    loadTemplates();
    
    // Load selected template
    $('#load-template-btn').on('click', function() {
        const templateName = $('#template-selector').val();
        if (!templateName) {
            CDDUNotifications.show(
                __('Please select a template to load', 'wp-cddu-manager'),
                'warning',
                'contract-notifications'
            );
            return;
        }
        
        $.post(cddu_ajax.ajax_url, {
            action: 'cddu_load_template',
            template_name: templateName,
            nonce: cddu_ajax.nonce
        }, function(response) {
            if (response.success) {
                // Set content in TinyMCE editor
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('contract_content')) {
                    tinyMCE.get('contract_content').setContent(response.data.content);
                } else {
                    $('#contract_content').val(response.data.content);
                }
                CDDUNotifications.show(
                    __('Template loaded successfully!', 'wp-cddu-manager'),
                    'success',
                    'contract-notifications'
                );
            } else {
                CDDUNotifications.show(
                    __('Error:', 'wp-cddu-manager') + ' ' + (response.data.message || __('Failed to load template', 'wp-cddu-manager')),
                    'error',
                    'contract-notifications'
                );
            }
        });
    });
    
    // Save current content as template
    $('#save-template-btn').on('click', function() {
        const templateName = $('#template-name').val();
        if (!templateName) {
            CDDUNotifications.show(
                __('Please enter a template name', 'wp-cddu-manager'),
                'warning',
                'contract-notifications'
            );
            return;
        }
        
        // Get current editor content
        let content = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('contract_content')) {
            content = tinyMCE.get('contract_content').getContent();
        } else {
            content = $('#contract_content').val();
        }
        
        if (!content) {
            CDDUNotifications.show(
                __('Please enter some content to save as template', 'wp-cddu-manager'),
                'warning',
                'contract-notifications'
            );
            return;
        }
        
        $.post(cddu_ajax.ajax_url, {
            action: 'cddu_save_template',
            template_name: templateName,
            template_content: content,
            nonce: cddu_ajax.nonce
        }, function(response) {
            if (response.success) {
                CDDUNotifications.show(
                    response.data.message,
                    'success',
                    'contract-notifications'
                );
                $('#template-name').val('');
                loadTemplates(); // Refresh template list
            } else {
                CDDUNotifications.show(
                    __('Error:', 'wp-cddu-manager') + ' ' + (response.data.message || __('Failed to save template', 'wp-cddu-manager')),
                    'error',
                    'contract-notifications'
                );
            }
        });
    });
    
});
