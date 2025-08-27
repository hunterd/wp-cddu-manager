/**
 * Mission Schedule & Budget JavaScript functionality
 */
jQuery(document).ready(function($) {
    let organizationDailyHours = 7.0; // Default value
    
    // Load organization data when organization changes
    $('#organization_id').on('change', function() {
        const organizationId = $(this).val();
        if (organizationId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cddu_get_organization_data',
                    organization_id: organizationId,
                    nonce: cdduMissionSchedule.nonce
                },
                success: function(response) {
                    if (response.success) {
                        organizationDailyHours = response.data.daily_working_hours;
                        $('#mission-daily-working-hours').text(organizationDailyHours + ' ' + cdduMissionSchedule.i18n.hoursPerDay);
                        calculateMissionStats(); // Recalculate with new daily hours
                    }
                },
                error: function() {
                    organizationDailyHours = 7.0; // Reset to default on error
                    $('#mission-daily-working-hours').text('7.0 ' + cdduMissionSchedule.i18n.hoursPerDay);
                    calculateMissionStats();
                }
            });
        } else {
            organizationDailyHours = 7.0;
            $('#mission-daily-working-hours').text('-');
            calculateMissionStats();
        }
    });
    
    // Mission calculations
    function calculateMissionStats() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const totalHours = parseFloat($('#total_hours').val()) || 0;
        const hourlyRate = parseFloat($('#hourly_rate').val()) || 0;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = Math.abs(end.getTime() - start.getTime());
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            
            $('#mission-duration').text(daysDiff + ' ' + cdduMissionSchedule.i18n.days);
            
            if (totalHours > 0) {
                const hoursPerDay = (totalHours / daysDiff).toFixed(1);
                $('#mission-hours-per-day').text(hoursPerDay + ' ' + cdduMissionSchedule.i18n.hoursPerDay);
                
                // Calculate working days based on organization's daily working hours
                const workingDays = (totalHours / organizationDailyHours).toFixed(1);
                $('#mission-working-days').text(workingDays + ' ' + cdduMissionSchedule.i18n.days);
            } else {
                $('#mission-hours-per-day').text('-');
                $('#mission-working-days').text('-');
            }
        } else {
            $('#mission-duration').text('-');
            $('#mission-hours-per-day').text('-');
            $('#mission-working-days').text('-');
        }
        
        if (totalHours > 0 && hourlyRate > 0) {
            const totalBudget = totalHours * hourlyRate;
            $('#mission-total-budget').text(totalBudget.toFixed(2) + ' €');
        } else {
            $('#mission-total-budget').text('-');
        }
    }
    
    $('#start_date, #end_date, #total_hours, #hourly_rate').on('change keyup', calculateMissionStats);
    
    // Load organization data on page load if organization is already selected
    const initialOrgId = $('#organization_id').val();
    if (initialOrgId) {
        $('#organization_id').trigger('change');
    } else {
        // Initial calculation with default values
        calculateMissionStats();
    }
});
