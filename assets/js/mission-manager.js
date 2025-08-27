/**
 * Mission Manager JavaScript
 */

(function($) {
    'use strict';

    let missionManager = {
        init: function() {
            this.bindEvents();
            this.initSkillsManager();
            this.initCalculations();
            this.initFilters();
            this.initSorting();
        },

        bindEvents: function() {
            // Mission actions
            $(document).on('click', '.view-mission', this.viewMission.bind(this));
            $(document).on('click', '.duplicate-mission', this.duplicateMission.bind(this));
            $(document).on('click', '.delete-mission', this.deleteMission.bind(this));
            
            // Modal
            $('.cddu-modal-close').on('click', this.closeModal.bind(this));
            $(window).on('click', function(event) {
                if (event.target.classList.contains('cddu-modal')) {
                    this.closeModal();
                }
            }.bind(this));
            
            // Filters
            $('#apply-filters').on('click', this.applyFilters.bind(this));
            $('#clear-filters').on('click', this.clearFilters.bind(this));
            
            // Auto-calculate on value changes
            $('#start_date, #end_date, #total_hours, #hourly_rate').on('change', this.updateCalculations.bind(this));
        },

        initSkillsManager: function() {
            const skillsArray = [];
            
            $('#add-skill-btn').on('click', function() {
                const skillInput = $('#new_skill');
                const skill = skillInput.val().trim();
                
                if (skill && !skillsArray.includes(skill)) {
                    skillsArray.push(skill);
                    this.renderSkills(skillsArray);
                    skillInput.val('');
                }
            }.bind(this));
            
            $('#new_skill').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    $('#add-skill-btn').click();
                }
            });
            
            $(document).on('click', '.remove-skill', function() {
                const skill = $(this).data('skill');
                const index = skillsArray.indexOf(skill);
                if (index > -1) {
                    skillsArray.splice(index, 1);
                    this.renderSkills(skillsArray);
                }
            }.bind(this));
            
            this.skillsArray = skillsArray;
        },

        renderSkills: function(skills) {
            const skillsList = $('#skills-list');
            skillsList.empty();
            
            skills.forEach(function(skill) {
                const skillTag = $('<span class="skill-tag">')
                    .text(skill)
                    .append($('<span class="remove-skill">').text('×').attr('data-skill', skill));
                skillsList.append(skillTag);
            });
            
            // Skills functionality removed - handled by WordPress native interface
        },

        initCalculations: function() {
            this.updateCalculations();
        },

        updateCalculations: function() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const totalHours = parseFloat($('#total_hours').val()) || 0;
            const hourlyRate = parseFloat($('#hourly_rate').val()) || 0;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                // Validate dates
                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                    $('#mission-duration').text('-');
                    $('#mission-hours-per-day').text('-');
                    return;
                }
                
                const timeDiff = end.getTime() - start.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Add 1 to include both start and end days
                
                // Ensure daysDiff is positive
                if (daysDiff <= 0) {
                    $('#mission-duration').text('0 days');
                    $('#mission-hours-per-day').text('-');
                    return;
                }
                
                const durationText = daysDiff + ' ' + (cddu_mission_ajax.strings?.days || 'days');
                $('#mission-duration').text(durationText);
                
                if (totalHours > 0 && daysDiff > 0) {
                    const hoursPerDay = (totalHours / daysDiff).toFixed(2);
                    $('#mission-hours-per-day').text(hoursPerDay + 'h');
                } else {
                    $('#mission-hours-per-day').text('-');
                }
            } else {
                $('#mission-duration').text('-');
                $('#mission-hours-per-day').text('-');
            }
            
            if (totalHours > 0 && hourlyRate > 0) {
                const totalBudget = (totalHours * hourlyRate).toFixed(2);
                $('#mission-total-budget').text(totalBudget + '€');
            } else {
                $('#mission-total-budget').text('-');
            }
        },

        initFilters: function() {
            // Store original table rows for filtering
            this.originalRows = $('#missions-table tbody tr').clone();
        },

        initSorting: function() {
            $('.sortable').on('click', function() {
                const column = $(this).data('sort');
                const isAsc = $(this).hasClass('asc');
                
                // Remove all sorting classes
                $('.sortable').removeClass('asc desc');
                
                // Add appropriate class
                if (isAsc) {
                    $(this).addClass('desc');
                    this.sortTable(column, 'desc');
                } else {
                    $(this).addClass('asc');
                    this.sortTable(column, 'asc');
                }
            }.bind(this));
        },

        sortTable: function(column, direction) {
            const tbody = $('#missions-table tbody');
            const rows = tbody.find('tr').toArray();
            
            rows.sort(function(a, b) {
                let aVal, bVal;
                
                switch(column) {
                    case 'title':
                        aVal = $(a).find('.mission-title strong').text().toLowerCase();
                        bVal = $(b).find('.mission-title strong').text().toLowerCase();
                        break;
                    case 'organization':
                        aVal = $(a).find('.organization').text().toLowerCase();
                        bVal = $(b).find('.organization').text().toLowerCase();
                        break;
                    case 'status':
                        aVal = $(a).attr('data-status');
                        bVal = $(b).attr('data-status');
                        break;
                    case 'priority':
                        const priorityOrder = {'low': 1, 'medium': 2, 'high': 3, 'critical': 4};
                        aVal = priorityOrder[$(a).attr('data-priority')] || 0;
                        bVal = priorityOrder[$(b).attr('data-priority')] || 0;
                        break;
                    case 'start_date':
                    case 'end_date':
                        aVal = $(a).find('.' + column.replace('_', '-')).attr('data-sort-value') || '';
                        bVal = $(b).find('.' + column.replace('_', '-')).attr('data-sort-value') || '';
                        break;
                    case 'total_hours':
                    case 'budget':
                        aVal = parseFloat($(a).find('.' + column.replace('_', '-')).attr('data-sort-value')) || 0;
                        bVal = parseFloat($(b).find('.' + column.replace('_', '-')).attr('data-sort-value')) || 0;
                        break;
                    default:
                        aVal = $(a).find('td').eq(0).text();
                        bVal = $(b).find('td').eq(0).text();
                }
                
                if (direction === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            tbody.empty().append(rows);
        },

        applyFilters: function() {
            const organizationFilter = $('#filter-organization').val();
            const statusFilter = $('#filter-status').val();
            const priorityFilter = $('#filter-priority').val();
            
            const tbody = $('#missions-table tbody');
            tbody.empty();
            
            let visibleCount = 0;
            
            this.originalRows.each(function() {
                const row = $(this);
                let showRow = true;
                
                if (organizationFilter && row.attr('data-organization-id') !== organizationFilter) {
                    showRow = false;
                }
                
                if (statusFilter && row.attr('data-status') !== statusFilter) {
                    showRow = false;
                }
                
                if (priorityFilter && row.attr('data-priority') !== priorityFilter) {
                    showRow = false;
                }
                
                if (showRow) {
                    tbody.append(row.clone());
                    visibleCount++;
                }
            });
            
            if (visibleCount === 0) {
                tbody.append('<tr><td colspan="9" class="no-missions">' + 
                    'No missions match the selected filters.' + 
                    '</td></tr>');
            }
            
            this.updateStats();
        },

        clearFilters: function() {
            $('#filter-organization, #filter-status, #filter-priority').val('');
            
            const tbody = $('#missions-table tbody');
            tbody.empty().append(this.originalRows.clone());
            
            this.updateStats();
        },

        updateStats: function() {
            const visibleRows = $('#missions-table tbody tr[data-mission-id]');
            $('#total-missions').text(visibleRows.length);
            
            const openMissions = visibleRows.filter('[data-status="open"]').length;
            $('#open-missions').text(openMissions);
            
            const inProgressMissions = visibleRows.filter('[data-status="in_progress"]').length;
            $('#in-progress-missions').text(inProgressMissions);
        },

        viewMission: function(e) {
            e.preventDefault();
            
            const missionId = $(e.target).data('mission-id');
            
            $.ajax({
                url: cddu_mission_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cddu_get_mission_data',
                    mission_id: missionId,
                    nonce: cddu_mission_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.showMissionModal(response.data.mission_data, response.data.mission_stats);
                    } else {
                        this.showMessage(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showMessage('Failed to load mission data.', 'error');
                }.bind(this)
            });
        },

        duplicateMission: function(e) {
            e.preventDefault();
            
            const missionId = $(e.target).data('mission-id');
            
            if (confirm('Are you sure you want to duplicate this mission?')) {
                // Implementation for duplicating mission
                this.showMessage('Mission duplication is not yet implemented.', 'error');
            }
        },

        deleteMission: function(e) {
            e.preventDefault();
            
            const missionId = $(e.target).data('mission-id');
            
            if (confirm(cddu_mission_ajax.strings.confirm_delete)) {
                $.ajax({
                    url: cddu_mission_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'cddu_delete_mission',
                        mission_id: missionId,
                        nonce: cddu_mission_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            this.showMessage(response.data.message, 'success');
                            $('tr[data-mission-id="' + missionId + '"]').fadeOut(function() {
                                $(this).remove();
                                this.updateStats();
                            }.bind(this));
                        } else {
                            this.showMessage(response.data.message, 'error');
                        }
                    }.bind(this),
                    error: function() {
                        this.showMessage('Failed to delete mission.', 'error');
                    }.bind(this)
                });
            }
        },

        showMissionModal: function(missionData, missionStats) {
            const modalBody = $('#modal-body');
            
            let html = '<div class="mission-details">';
            html += '<h3>' + missionData.title + '</h3>';
            
            if (missionData.description) {
                html += '<div class="mission-description">' + missionData.description + '</div>';
            }
            
            html += '<div class="mission-info-grid">';
            html += '<div class="info-item"><strong>Status:</strong> ' + (missionData.status || 'Draft') + '</div>';
            html += '<div class="info-item"><strong>Priority:</strong> ' + (missionData.priority || 'Medium') + '</div>';
            html += '<div class="info-item"><strong>Location:</strong> ' + (missionData.location || 'Not specified') + '</div>';
            html += '<div class="info-item"><strong>Start Date:</strong> ' + (missionData.start_date || 'Not set') + '</div>';
            html += '<div class="info-item"><strong>End Date:</strong> ' + (missionData.end_date || 'Not set') + '</div>';
            html += '<div class="info-item"><strong>Total Hours:</strong> ' + (missionData.total_hours || 0) + 'h</div>';
            html += '<div class="info-item"><strong>Hourly Rate:</strong> ' + (missionData.hourly_rate || 0) + '€</div>';
            
            if (missionStats && missionStats.total_budget) {
                html += '<div class="info-item"><strong>Total Budget:</strong> ' + missionStats.total_budget + '€</div>';
            }
            
            html += '</div>';
            
            if (missionData.required_skills && missionData.required_skills.length > 0) {
                html += '<div class="required-skills">';
                html += '<strong>Required Skills:</strong><br>';
                missionData.required_skills.forEach(function(skill) {
                    html += '<span class="skill-tag">' + skill + '</span>';
                });
                html += '</div>';
            }
            
            html += '</div>';
            
            modalBody.html(html);
            $('#cddu-mission-modal').show();
        },

        closeModal: function() {
            $('#cddu-mission-modal').hide();
        },

        showLoading: function() {
            $('#create-mission-btn').prop('disabled', true).text(cddu_mission_ajax.strings.loading);
        },

        hideLoading: function() {
            $('#create-mission-btn').prop('disabled', false).text('Create Mission');
        },

        showMessage: function(message, type) {
            const messageContainer = $('#cddu-mission-messages');
            const messageElement = $('<div class="cddu-message ' + type + '">' + message + '</div>');
            
            messageContainer.empty().append(messageElement);
            
            setTimeout(function() {
                messageElement.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        missionManager.init();
    });

})(jQuery);
