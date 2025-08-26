/**
 * Addendum Manager JavaScript
 * Handles addendum creation form functionality
 */

jQuery(document).ready(function($) {
    let weekCounter = 0;
    
    // Calculate addendum values
    $('#calculate-addendum-btn').on('click', function() {
        const hourlyRate = parseFloat($('#hourly_rate').val()) || 13.17;
        
        const novemberHours = parseFloat($('#november_total_hours').val()) || 0;
        const decemberHours = parseFloat($('#december_total_hours').val()) || 0;
        const januaryHours = parseFloat($('#january_total_hours').val()) || 0;
        
        const novemberAmount = novemberHours * hourlyRate;
        const decemberAmount = decemberHours * hourlyRate;
        const januaryAmount = januaryHours * hourlyRate;
        const totalAmount = novemberAmount + decemberAmount + januaryAmount;
        
        $('#calc-november-amount').text(novemberAmount.toFixed(2) + ' €');
        $('#calc-december-amount').text(decemberAmount.toFixed(2) + ' €');
        $('#calc-january-amount').text(januaryAmount.toFixed(2) + ' €');
        $('#calc-total-amount').text(totalAmount.toFixed(2) + ' €');
        
        $('#calculation-results').show();
    });
    
    // Generate addendum
    $('#generate-addendum-btn').on('click', function() {
        const formData = $('#addendum-form').serialize();
        
        $.ajax({
            url: cddu_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=cddu_generate_addendum&nonce=' + cddu_ajax.nonce,
            beforeSend: function() {
                $('#generate-addendum-btn').prop('disabled', true).text('Generating...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Addendum created successfully!');
                    if (response.data.edit_url) {
                        window.location.href = response.data.edit_url;
                    }
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Ajax error occurred');
            },
            complete: function() {
                $('#generate-addendum-btn').prop('disabled', false).text('Generate Addendum PDF');
            }
        });
    });
    
    // Preview addendum
    $('#preview-addendum-btn').on('click', function() {
        const formData = $('#addendum-form').serialize();
        
        $.ajax({
            url: cddu_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=cddu_preview_addendum&nonce=' + cddu_ajax.nonce,
            beforeSend: function() {
                $('#preview-addendum-btn').prop('disabled', true).text('Generating Preview...');
            },
            success: function(response) {
                if (response.success) {
                    // Open preview in new window
                    const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                    previewWindow.document.write(response.data.html);
                    previewWindow.document.close();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Ajax error occurred');
            },
            complete: function() {
                $('#preview-addendum-btn').prop('disabled', false).text('Preview Addendum');
            }
        });
    });
    
    // Add week functionality
    $('#add-week-btn').on('click', function() {
        weekCounter++;
        const weekItem = `
            <div class="weekly-schedule-item" data-week="${weekCounter}">
                <button type="button" class="remove-week-btn" onclick="removeWeek(${weekCounter})">×</button>
                <h4>Week ${weekCounter}</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_start_date">Start Date</label>
                        </th>
                        <td>
                            <input type="date" id="week_${weekCounter}_start_date" name="weeks[${weekCounter}][start_date]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_end_date">End Date</label>
                        </th>
                        <td>
                            <input type="date" id="week_${weekCounter}_end_date" name="weeks[${weekCounter}][end_date]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_af_hours">AF Hours</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="week_${weekCounter}_af_hours" name="weeks[${weekCounter}][af_hours]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_pr_hours">PR Hours</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="week_${weekCounter}_pr_hours" name="weeks[${weekCounter}][pr_hours]" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
        `;
        $('#weekly-schedule-container').append(weekItem);
    });
    
    // Auto-populate instructor details when selected
    $('#instructor_user_id').on('change', function() {
        const userId = $(this).val();
        if (userId) {
            // Get instructor data via AJAX
            $.ajax({
                url: cddu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cddu_get_instructor_data',
                    user_id: userId,
                    nonce: cddu_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        if (data.display_name) {
                            $('#instructor_full_name').val(data.display_name);
                        }
                        // Populate other fields if available in user meta
                        if (data.address) {
                            $('#instructor_address').val(data.address);
                        }
                        if (data.birth_date) {
                            $('#instructor_birth_date').val(data.birth_date);
                        }
                        if (data.birth_place) {
                            $('#instructor_birth_place').val(data.birth_place);
                        }
                        if (data.social_security) {
                            $('#instructor_social_security').val(data.social_security);
                        }
                    }
                }
            });
        }
    });
    
    // Auto-populate contract details when original contract is selected
    $('#original_contract_id').on('change', function() {
        const contractId = $(this).val();
        if (contractId) {
            // Get contract data via AJAX
            $.ajax({
                url: cddu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cddu_get_contract_data',
                    contract_id: contractId,
                    nonce: cddu_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        if (data.instructor_user_id) {
                            $('#instructor_user_id').val(data.instructor_user_id).trigger('change');
                        }
                        if (data.start_date) {
                            $('#original_contract_date').val(data.start_date);
                        }
                        if (data.end_date) {
                            $('#original_end_date').val(data.end_date);
                        }
                        if (data.hourly_rate) {
                            $('#hourly_rate').val(data.hourly_rate);
                        }
                    }
                }
            });
        }
    });
    
    // Auto-calculate PR hours when AF hours change
    $('.monthly-breakdown-item input[id$="_af_hours"]').on('input', function() {
        const $this = $(this);
        const month = $this.attr('id').replace('_af_hours', '');
        const $totalHours = $('#' + month + '_total_hours');
        const $prHours = $('#' + month + '_pr_hours');
        
        const totalHours = parseFloat($totalHours.val()) || 0;
        const afHours = parseFloat($this.val()) || 0;
        const prHours = totalHours - afHours;
        
        if (prHours >= 0) {
            $prHours.val(prHours.toFixed(2));
        }
    });
    
    // Auto-calculate AF hours when PR hours change
    $('.monthly-breakdown-item input[id$="_pr_hours"]').on('input', function() {
        const $this = $(this);
        const month = $this.attr('id').replace('_pr_hours', '');
        const $totalHours = $('#' + month + '_total_hours');
        const $afHours = $('#' + month + '_af_hours');
        
        const totalHours = parseFloat($totalHours.val()) || 0;
        const prHours = parseFloat($this.val()) || 0;
        const afHours = totalHours - prHours;
        
        if (afHours >= 0) {
            $afHours.val(afHours.toFixed(2));
        }
    });
    
    // Auto-calculate total when AF or PR hours change
    $('.monthly-breakdown-item input[id$="_af_hours"], .monthly-breakdown-item input[id$="_pr_hours"]').on('input', function() {
        const $this = $(this);
        const month = $this.attr('id').replace(/_[ap][fr]_hours$/, '');
        const $totalHours = $('#' + month + '_total_hours');
        const $afHours = $('#' + month + '_af_hours');
        const $prHours = $('#' + month + '_pr_hours');
        
        const afHours = parseFloat($afHours.val()) || 0;
        const prHours = parseFloat($prHours.val()) || 0;
        const totalHours = afHours + prHours;
        
        $totalHours.val(totalHours.toFixed(2));
    });
});

// Global function to remove weeks
function removeWeek(weekNumber) {
    jQuery(`.weekly-schedule-item[data-week="${weekNumber}"]`).remove();
}
