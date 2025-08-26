jQuery(document).ready(function($) {
    
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
            nonce: cddu_ajax.nonce
        };
        
        if (!formData.annual_hours || !formData.hourly_rate || !formData.start_date || !formData.end_date) {
            alert('Please fill in all required fields (Annual hours, Hourly rate, Start date, End date)');
            return;
        }
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                const calc = response.data;
                $('#calc-nb-weeks').text(calc.nb_weeks);
                $('#calc-intensity').text(calc.intensity_formatted || (calc.intensity + ' h/week'));
                $('#calc-hp').text(calc.hp_formatted || (calc.hp + ' h'));
                $('#calc-ht').text(calc.ht_formatted || (calc.ht + ' h'));
                $('#calc-gross').text(calc.gross_formatted || (calc.gross + ' €'));
                $('#calc-bonus').text(calc.bonus_formatted || (calc.bonus + ' €'));
                $('#calc-paid-leave').text(calc.paid_leave_formatted || (calc.paid_leave + ' €'));
                $('#calc-total').text(calc.total_formatted || (calc.total + ' €'));
                
                $('#calculation-results').show();
            } else {
                alert('Error: ' + (response.data.message || 'Calculation failed'));
            }
        });
    });
    
    // Generate contract
    $('#generate-contract-btn').on('click', function() {
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
            nonce: cddu_ajax.nonce
        };
        
        if (!formData.organization_id || !formData.instructor_user_id) {
            alert('Please select an organization and instructor');
            return;
        }
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
                if (confirm('Contract created successfully! Would you like to edit it now?')) {
                    window.location.href = response.data.edit_url;
                }
            } else {
                alert('Error: ' + (response.data.message || 'Contract generation failed'));
            }
        });
    });
    
    // Preview contract (placeholder)
    $('#preview-contract-btn').on('click', function() {
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
            nonce: cddu_ajax.nonce
        };
        
        $.post(cddu_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                // Open preview in new window
                const previewWindow = window.open('', '_blank');
                previewWindow.document.write(response.data.html);
                previewWindow.document.close();
            } else {
                alert('Error: ' + (response.data.message || 'Preview failed'));
            }
        });
    });
    
});
