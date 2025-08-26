jQuery(document).ready(function($) {
    let currentOrganizationId = 0;
    let assignedInstructors = [];
    
    // Load organization data
    $('#load-organization').on('click', function() {
        const organizationId = $('#organization-select').val();
        
        if (!organizationId) {
            showMessage(__('Please select an organization first'), 'error');
            return;
        }
        
        loadOrganizationData(organizationId);
    });
    
    // Search instructors
    $('#search-instructors').on('click', function() {
        if (!currentOrganizationId) {
            showMessage(__('Please select an organization first'), 'error');
            return;
        }
        
        searchInstructors();
    });
    
    // Search on Enter key
    $('#instructor-search').on('keypress', function(e) {
        if (e.which === 13) {
            $('#search-instructors').click();
        }
    });
    
    // Real-time search (debounced)
    let searchTimeout;
    $('#instructor-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (currentOrganizationId) {
                searchInstructors();
            }
        }, 300);
    });
    
    function loadOrganizationData(organizationId) {
        showLoading(true);
        currentOrganizationId = organizationId;
        
        $.ajax({
            url: cddu_instructor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cddu_get_organization_instructors',
                organization_id: organizationId,
                nonce: cddu_instructor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayOrganizationDetails(response.data.organization);
                    displayAssignedInstructors(response.data.instructors);
                    assignedInstructors = response.data.instructors.map(i => i.id);
                    
                    // Show sections
                    $('#organization-details').show();
                    $('#assigned-instructors-section').show();
                    $('#add-instructor-section').show();
                    
                    // Auto-search for available instructors
                    searchInstructors();
                } else {
                    showMessage(response.data.message || cddu_instructor_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                showMessage(cddu_instructor_ajax.strings.error_occurred, 'error');
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function displayOrganizationDetails(organization) {
        const html = `
            <table class="form-table">
                <tr>
                    <th scope="row">${__('Name')}</th>
                    <td>${escapeHtml(organization.name || organization.title)}</td>
                </tr>
                <tr>
                    <th scope="row">${__('Address')}</th>
                    <td>${escapeHtml(organization.address || __('Not specified'))}</td>
                </tr>
                <tr>
                    <th scope="row">${__('Legal Representative')}</th>
                    <td>${escapeHtml(organization.legal_representative || __('Not specified'))}</td>
                </tr>
            </table>
        `;
        $('#organization-info').html(html);
    }
    
    function displayAssignedInstructors(instructors) {
        if (instructors.length === 0) {
            $('#assigned-instructors-list').html(`
                <p class="description">${__('No instructors assigned to this organization.')}</p>
            `);
            return;
        }
        
        let html = '<div class="instructor-results">';
        instructors.forEach(function(instructor) {
            html += createInstructorItem(instructor, true);
        });
        html += '</div>';
        
        $('#assigned-instructors-list').html(html);
    }
    
    function searchInstructors() {
        if (!currentOrganizationId) return;
        
        const search = $('#instructor-search').val();
        showLoading(true);
        
        $.ajax({
            url: cddu_instructor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cddu_search_instructors',
                search: search,
                organization_id: currentOrganizationId,
                nonce: cddu_instructor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displaySearchResults(response.data.data);
                } else {
                    showMessage(response.data.message || cddu_instructor_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                showMessage(cddu_instructor_ajax.strings.error_occurred, 'error');
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function displaySearchResults(instructors) {
        if (instructors.length === 0) {
            $('#instructor-search-results').html(`
                <p class="description">${cddu_instructor_ajax.strings.no_results}</p>
            `);
            return;
        }
        
        let html = '';
        instructors.forEach(function(instructor) {
            html += createInstructorItem(instructor, instructor.is_assigned);
        });
        
        $('#instructor-search-results').html(html);
    }
    
    function createInstructorItem(instructor, isAssigned) {
        const fullName = instructor.full_name || `${instructor.first_name || ''} ${instructor.last_name || ''}`.trim() || instructor.title;
        const address = instructor.address || __('No address specified');
        const contractsCount = instructor.active_contracts || 0;
        const assignedDate = instructor.assigned_date ? new Date(instructor.assigned_date).toLocaleDateString() : '';
        
        return `
            <div class="instructor-item ${isAssigned ? 'assigned-instructor' : ''}" data-instructor-id="${instructor.id}">
                <div class="instructor-info">
                    <h4>${escapeHtml(fullName)}</h4>
                    <div class="details">
                        <div>${escapeHtml(address)}</div>
                        ${isAssigned ? `<div class="instructor-stats">
                            ${assignedDate ? `${__('Assigned')}: ${assignedDate}` : ''}
                            ${contractsCount > 0 ? `<span class="contracts-count">${contractsCount} ${__('active contracts')}</span>` : ''}
                        </div>` : ''}
                    </div>
                </div>
                <div class="instructor-actions">
                    ${!isAssigned ? `
                        <button type="button" class="button button-primary assign-btn" 
                                data-instructor-id="${instructor.id}" 
                                data-instructor-name="${escapeHtml(fullName)}">
                            ${__('Assign')}
                        </button>
                    ` : `
                        <button type="button" class="button button-secondary unassign-btn" 
                                data-instructor-id="${instructor.id}" 
                                data-instructor-name="${escapeHtml(fullName)}"
                                ${contractsCount > 0 ? 'disabled title="' + __('Cannot unassign instructor with active contracts') + '"' : ''}>
                            ${__('Unassign')}
                        </button>
                    `}
                </div>
            </div>
        `;
    }
    
    // Handle assign button clicks
    $(document).on('click', '.assign-btn', function() {
        const instructorId = $(this).data('instructor-id');
        const instructorName = $(this).data('instructor-name');
        
        if (!confirm(`${__('Assign instructor')} "${instructorName}" ${__('to this organization')}?`)) {
            return;
        }
        
        assignInstructor(instructorId, instructorName);
    });
    
    // Handle unassign button clicks
    $(document).on('click', '.unassign-btn', function() {
        if ($(this).prop('disabled')) return;
        
        const instructorId = $(this).data('instructor-id');
        const instructorName = $(this).data('instructor-name');
        
        if (!confirm(`${cddu_instructor_ajax.strings.confirm_unassign} "${instructorName}"?`)) {
            return;
        }
        
        unassignInstructor(instructorId, instructorName);
    });
    
    function assignInstructor(instructorId, instructorName) {
        showLoading(true);
        
        $.ajax({
            url: cddu_instructor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cddu_assign_instructor',
                organization_id: currentOrganizationId,
                instructor_id: instructorId,
                nonce: cddu_instructor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(`${cddu_instructor_ajax.strings.assign_success}: ${instructorName}`, 'success');
                    // Reload organization data to refresh lists
                    loadOrganizationData(currentOrganizationId);
                } else {
                    showMessage(response.data.message || cddu_instructor_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                showMessage(cddu_instructor_ajax.strings.error_occurred, 'error');
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function unassignInstructor(instructorId, instructorName) {
        showLoading(true);
        
        $.ajax({
            url: cddu_instructor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cddu_unassign_instructor',
                organization_id: currentOrganizationId,
                instructor_id: instructorId,
                nonce: cddu_instructor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(`${cddu_instructor_ajax.strings.unassign_success}: ${instructorName}`, 'success');
                    // Reload organization data to refresh lists
                    loadOrganizationData(currentOrganizationId);
                } else {
                    showMessage(response.data.message || cddu_instructor_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                showMessage(cddu_instructor_ajax.strings.error_occurred, 'error');
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function showLoading(show) {
        if (show) {
            $('#loading-indicator').show();
        } else {
            $('#loading-indicator').hide();
        }
    }
    
    function showMessage(message, type) {
        const alertClass = type === 'error' ? 'notice-error' : 'notice-success';
        const html = `<div class="notice ${alertClass} is-dismissible"><p>${message}</p></div>`;
        
        $('#message-container').html(html);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('#message-container .notice').fadeOut();
        }, 5000);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $('#message-container').offset().top - 50
        }, 300);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function __(text) {
        return cddu_instructor_ajax.strings[text] || text;
    }
});
