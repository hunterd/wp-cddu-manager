jQuery(document).ready(function($) {
    // Skills management
    $('#add_skill_btn').on('click', function() {
        var skill = $('#new_skill_input').val().trim();
        if (skill) {
            addSkill(skill);
            $('#new_skill_input').val('');
        }
    });
    
    $('#new_skill_input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#add_skill_btn').click();
        }
    });
    
    $(document).on('click', '.remove-skill', function() {
        $(this).parent('.skill-tag').remove();
    });
    
    function addSkill(skill) {
        var skillExists = false;
        $('.skill-tag').each(function() {
            if ($(this).find('input').val() === skill) {
                skillExists = true;
                return false;
            }
        });
        
        if (!skillExists) {
            var skillTag = $('<span class="skill-tag">' + 
                skill + 
                '<span class="remove-skill" data-skill="' + skill + '">&times;</span>' +
                '<input type="hidden" name="mission[required_skills][]" value="' + skill + '" />' +
                '</span>');
            $('#skills_list').append(skillTag);
        }
    }
    
    // Mission calculations
    function updateCalculations() {
        var startDate = $('#mission_start_date').val();
        var endDate = $('#mission_end_date').val();
        var totalHours = parseFloat($('#mission_total_hours').val()) || 0;
        var hourlyRate = parseFloat($('#mission_hourly_rate').val()) || 0;
        
        if (startDate && endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);
            
            // Validate dates
            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                $('#calc_duration').text('-');
                $('#calc_hours_per_day').text('-');
                return;
            }
            
            var timeDiff = end.getTime() - start.getTime();
            var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Add 1 to include both start and end days
            
            // Ensure daysDiff is positive
            if (daysDiff <= 0) {
                $('#calc_duration').text('0 days');
                $('#calc_hours_per_day').text('-');
                return;
            }
            
            $('#calc_duration').text(daysDiff + ' days');
            
            if (totalHours > 0 && daysDiff > 0) {
                var hoursPerDay = (totalHours / daysDiff).toFixed(2);
                $('#calc_hours_per_day').text(hoursPerDay + 'h');
            } else {
                $('#calc_hours_per_day').text('-');
            }
        } else {
            $('#calc_duration').text('-');
            $('#calc_hours_per_day').text('-');
        }
        
        if (totalHours > 0 && hourlyRate > 0) {
            var totalBudget = (totalHours * hourlyRate).toFixed(2);
            $('#calc_budget').text(totalBudget + '€');
        } else {
            $('#calc_budget').text('-');
        }
    }
    
    $('#mission_start_date, #mission_end_date, #mission_total_hours, #mission_hourly_rate').on('change', updateCalculations);
    
    // Initial calculation
    updateCalculations();
});
