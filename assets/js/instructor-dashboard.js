jQuery(document).ready(function($) {
    
    // Submit timesheet form
    $('#timesheet-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'cddu_submit_timesheet',
            contract_id: $('#contract_id').val(),
            month: $('#month').val(),
            year: $('#year').val(),
            hours_worked: $('#hours_worked').val(),
            description: $('#description').val(),
            nonce: cddu_instructor_ajax.nonce
        };
        
        // Validate required fields
        if (!formData.contract_id || !formData.month || !formData.year || !formData.hours_worked) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.text('Submitting...').prop('disabled', true);
        
        $.post(cddu_instructor_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
                // Reset form
                $('#timesheet-form')[0].reset();
                // Reload page to show new timesheet in the table
                window.location.reload();
            } else {
                alert('Error: ' + (response.data.message || 'Submission failed'));
            }
        }).always(function() {
            // Re-enable submit button
            submitBtn.text(originalText).prop('disabled', false);
        });
    });
    
    // Auto-fill hours based on contract when contract is selected
    $('#contract_id').on('change', function() {
        const contractId = $(this).val();
        if (!contractId) return;
        
        // This could be enhanced to fetch contract details and suggest hours
        // For now, we'll just clear the hours field
        $('#hours_worked').val('');
    });
    
    // Add some helper functionality for better UX
    
    // Format hours input to accept decimals
    $('#hours_worked').on('input', function() {
        let value = $(this).val();
        // Allow only numbers and decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Prevent multiple decimal points
        if (value.split('.').length > 2) {
            value = value.substring(0, value.lastIndexOf('.'));
        }
        $(this).val(value);
    });
    
    // Character counter for description
    $('#description').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        
        // Add character counter if it doesn't exist
        if (!$(this).siblings('.char-counter').length) {
            $(this).after('<div class="char-counter"></div>');
        }
        
        const counter = $(this).siblings('.char-counter');
        counter.text(currentLength + '/' + maxLength + ' characters');
        
        if (currentLength > maxLength) {
            counter.css('color', 'red');
            $(this).val($(this).val().substring(0, maxLength));
        } else {
            counter.css('color', '#666');
        }
    });
    
});
