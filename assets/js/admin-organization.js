/**
 * Organization Instructors and Managers Management
 * Handles instructor and manager assignment interfaces in organization edit pages
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeInstructorManagement();
        initializeManagerManagement();
    });

    function initializeInstructorManagement() {
        initializeManagement('instructor', '#cddu-organization-instructors');
    }

    function initializeManagerManagement() {
        initializeManagement('manager', '#cddu-organization-managers');
    }

    function initializeManagement(type, containerSelector) {
        const $container = $(containerSelector);
        
        if ($container.length === 0) {
            return;
        }

        initializeSearch(type);
        initializeFilters(type);
        initializeSelectButtons(type);
        initializeCheckboxHandlers(type);
        updateCounters(type);
        
        // Auto-update counters when checkboxes change
        $container.on('change', 'input[type="checkbox"]', function() {
            updateCounters(type);
        });
    }

    function initializeSearch(type) {
        const $searchInput = $(`#${type}-search`);
        const $clearButton = $(`#clear-${type}-search`);
        
        if ($searchInput.length === 0) {
            return;
        }

        let searchTimeout;
        
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(type);
            }, 300);
        });

        $clearButton.on('click', function() {
            $searchInput.val('');
            performSearch(type);
            $searchInput.focus();
        });
        
        function performSearch(type) {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const $items = $(`.${type}-item`);
            
            $items.each(function() {
                const $item = $(this);
                const itemData = $item.data(`${type}-name`) || '';
                
                if (searchTerm === '' || itemData.indexOf(searchTerm) > -1) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });

            updateCounters(type);
        }
    }

    function initializeFilters(type) {
        const $filterSelect = $(`#${type}-filter`);
        
        if ($filterSelect.length === 0) {
            return;
        }

        $filterSelect.on('change', function() {
            const filterValue = $(this).val();
            const $items = $(`.${type}-item`);
            
            $items.each(function() {
                const $item = $(this);
                const isAssigned = $item.data('assigned') === 'true';
                const hasSpecialAttribute = type === 'instructor' 
                    ? $item.data('has-contracts') === 'true'
                    : $item.data('has-other-orgs') === 'true';
                let shouldShow = true;
                
                switch (filterValue) {
                    case 'assigned':
                        shouldShow = isAssigned;
                        break;
                    case 'available':
                        shouldShow = !isAssigned;
                        break;
                    case 'with-contracts': // for instructors
                        shouldShow = hasSpecialAttribute;
                        break;
                    case 'experienced': // for managers
                        shouldShow = hasSpecialAttribute;
                        break;
                    case 'all':
                    default:
                        shouldShow = true;
                        break;
                }
                
                if (shouldShow) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });

            updateCounters(type);
        });
    }

    function initializeSelectButtons(type) {
        const $selectAllBtn = $(`#select-all-${type}s`);
        const $deselectAllBtn = $(`#deselect-all-${type}s`);
        const $selectVisibleBtn = $(`#select-visible-${type}s`);
        
        $selectAllBtn.on('click', function() {
            setAllCheckboxes(type, true);
        });

        $deselectAllBtn.on('click', function() {
            setAllCheckboxes(type, false);
        });
        
        $selectVisibleBtn.on('click', function() {
            setVisibleCheckboxes(type, true);
        });
        
        function setAllCheckboxes(type, checked) {
            $(`.${type}-item input[type="checkbox"]`).each(function() {
                setCheckboxState($(this), checked, type);
            });
            updateCounters(type);
        }
        
        function setVisibleCheckboxes(type, checked) {
            $(`.${type}-item:visible input[type="checkbox"]`).each(function() {
                setCheckboxState($(this), checked, type);
            });
            updateCounters(type);
        }
    }

    function initializeCheckboxHandlers(type) {
        // Handle checkbox change events with validation
        $(document).on('change', `.${type}-item input[type="checkbox"]`, function() {
            const $checkbox = $(this);
            const $item = $checkbox.closest(`.${type}-item`);
            const hasSpecialAttribute = type === 'instructor' 
                ? $item.data('has-contracts') === 'true'
                : false; // Managers don't need special validation
            const isChecked = $checkbox.is(':checked');
            
            // Prevent unchecking instructors with active contracts
            if (type === 'instructor' && !isChecked && hasSpecialAttribute) {
                if (!confirm(__('This instructor has active contracts. Are you sure you want to unassign them? This may affect ongoing contracts.', 'wp-cddu-manager'))) {
                    $checkbox.prop('checked', true);
                    return;
                }
            }
            
            // Update visual state
            updateItemVisualState($item, isChecked, type);
        });
    }

    function setCheckboxState($checkbox, isChecked, type) {
        $checkbox.prop('checked', isChecked);
        const $item = $checkbox.closest(`.${type}-item`);
        updateItemVisualState($item, isChecked, type);
    }
    
    function updateItemVisualState($item, isAssigned, type) {
        if (isAssigned) {
            $item.addClass('assigned').removeClass('available');
            $item.data('assigned', 'true');
        } else {
            $item.addClass('available').removeClass('assigned');
            $item.data('assigned', 'false');
        }
    }

    function updateCounters(type) {
        const $allItems = $(`.${type}-item`);
        const $visibleItems = $(`.${type}-item:visible`);
        const $selectedItems = $(`.${type}-item input[type="checkbox"]:checked`);
        const $visibleSelectedItems = $(`.${type}-item:visible input[type="checkbox"]:checked`);
        
        // Update results count
        const totalVisible = $visibleItems.length;
        const totalAll = $allItems.length;
        const $resultsCount = $(`#${type}-results-count`);
        if ($resultsCount.length) {
            $resultsCount.text(
                totalVisible === totalAll 
                    ? __('Showing all', 'wp-cddu-manager') + ` ${totalAll} ${type}s`
                    : __('Showing', 'wp-cddu-manager') + ` ${totalVisible} ` + __('of', 'wp-cddu-manager') + ` ${totalAll} ${type}s`
            );
        }
        
        // Update selected count
        const selectedCount = $selectedItems.length;
        const visibleSelectedCount = $visibleSelectedItems.length;
        const $selectedCount = $(`#${type}-selected-count`);
        if ($selectedCount.length) {
            $selectedCount.text(
                selectedCount === visibleSelectedCount
                    ? `${selectedCount} ` + __('selected', 'wp-cddu-manager')
                    : `${selectedCount} ` + __('selected', 'wp-cddu-manager') + ` (${visibleSelectedCount} ` + __('visible', 'wp-cddu-manager') + `)`
            );
        }
        
        // Update summary stats if they exist
        updateSummaryStats(type);
    }
    
    function updateSummaryStats(type) {
        const containerSelector = type === 'instructor' ? '#cddu-organization-instructors' : '#cddu-organization-managers';
        const $container = $(containerSelector);
        const $assignedCount = $container.find('.cddu-stat-item:first-child .cddu-stat-number');
        const $availableCount = $container.find('.cddu-stat-item:nth-child(2) .cddu-stat-number');
        
        if ($assignedCount.length && $availableCount.length) {
            const assignedTotal = $(`.${type}-item input[type="checkbox"]:checked`).length;
            const totalItems = $(`.${type}-item`).length;
            
            $assignedCount.text(assignedTotal);
            $availableCount.text(totalItems - assignedTotal);
        }
    }

    // Utility functions for translations
    function __(text) {
        return wp.i18n.__ ? wp.i18n.__(text, 'wp-cddu-manager') : text;
    }

    // Expose functions globally for potential external use
    window.CDDUInstructorManagement = {
        selectAll: function() {
            $('#select-all-instructors').trigger('click');
        },
        deselectAll: function() {
            $('#deselect-all-instructors').trigger('click');
        },
        selectVisible: function() {
            $('#select-visible-instructors').trigger('click');
        },
        clearSearch: function() {
            $('#clear-instructor-search').trigger('click');
        },
        setFilter: function(filterValue) {
            $('#instructor-filter').val(filterValue).trigger('change');
        },
        getSelectedInstructors: function() {
            const selected = [];
            $('.instructor-item input[type="checkbox"]:checked').each(function() {
                selected.push({
                    id: $(this).val(),
                    name: $(this).closest('.instructor-item').find('.instructor-name').text().trim()
                });
            });
            return selected;
        },
        getStats: function() {
            const $allItems = $('.instructor-item');
            const $selectedItems = $('.instructor-item input[type="checkbox"]:checked');
            const $visibleItems = $('.instructor-item:visible');
            
            return {
                total: $allItems.length,
                assigned: $selectedItems.length,
                available: $allItems.length - $selectedItems.length,
                visible: $visibleItems.length
            };
        }
    };

    window.CDDUManagerManagement = {
        selectAll: function() {
            $('#select-all-managers').trigger('click');
        },
        deselectAll: function() {
            $('#deselect-all-managers').trigger('click');
        },
        selectVisible: function() {
            $('#select-visible-managers').trigger('click');
        },
        clearSearch: function() {
            $('#clear-manager-search').trigger('click');
        },
        setFilter: function(filterValue) {
            $('#manager-filter').val(filterValue).trigger('change');
        },
        getSelectedManagers: function() {
            const selected = [];
            $('.manager-item input[type="checkbox"]:checked').each(function() {
                selected.push({
                    id: $(this).val(),
                    name: $(this).closest('.manager-item').find('.manager-name').text().trim()
                });
            });
            return selected;
        },
        getStats: function() {
            const $allItems = $('.manager-item');
            const $selectedItems = $('.manager-item input[type="checkbox"]:checked');
            const $visibleItems = $('.manager-item:visible');
            
            return {
                total: $allItems.length,
                assigned: $selectedItems.length,
                available: $allItems.length - $selectedItems.length,
                visible: $visibleItems.length
            };
        }
    };

    // Organization Metabox Validation
    function initializeOrganizationValidation() {
        // Daily working hours validation
        $('#daily_working_hours').on('change blur', function() {
            const value = parseFloat($(this).val());
            const $input = $(this);

            if (isNaN(value) || value === '') {
                $input.val(7);
                showValidationMessage('daily_working_hours', wp.i18n.__('Invalid value. Reset to default: 7 hours', 'wp-cddu-manager'), 'warning');
            } else if (value < 1) {
                $input.val(1);
                showValidationMessage('daily_working_hours', wp.i18n.__('Daily working hours cannot be less than 1 hour. Value adjusted to 1 hour.', 'wp-cddu-manager'), 'error');
            } else if (value > 24) {
                $input.val(24);
                showValidationMessage('daily_working_hours', wp.i18n.__('Daily working hours cannot exceed 24 hours. Value adjusted to 24 hours.', 'wp-cddu-manager'), 'error');
            } else {
                hideValidationMessage('daily_working_hours');
            }
        });

        // Working days per week validation
        $('#working_days_per_week').on('change blur', function() {
            const value = parseInt($(this).val());
            const $input = $(this);

            if (isNaN(value) || value === '') {
                $input.val(5);
                showValidationMessage('working_days_per_week', wp.i18n.__('Invalid value. Reset to default: 5 days', 'wp-cddu-manager'), 'warning');
            } else if (value < 1) {
                $input.val(1);
                showValidationMessage('working_days_per_week', wp.i18n.__('Working days per week cannot be less than 1 day. Value adjusted to 1 day.', 'wp-cddu-manager'), 'error');
            } else if (value > 7) {
                $input.val(7);
                showValidationMessage('working_days_per_week', wp.i18n.__('Working days per week cannot exceed 7 days. Value adjusted to 7 days.', 'wp-cddu-manager'), 'error');
            } else {
                hideValidationMessage('working_days_per_week');
            }
        });

        // Hide messages when user starts typing
        $('#daily_working_hours, #working_days_per_week').on('input', function() {
            const elementId = $(this).attr('id');
            hideValidationMessage(elementId);
        });
    }

    function showValidationMessage(elementId, message, type = 'error') {
        const $messageDiv = $('#' + elementId + '_message');
        $messageDiv
            .removeClass('validation-message error warning')
            .addClass('validation-message ' + type)
            .html(message)
            .fadeIn(300);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $messageDiv.fadeOut(300);
        }, 5000);
    }

    function hideValidationMessage(elementId) {
        $('#' + elementId + '_message').fadeOut(300);
    }

    // Initialize organization validation when DOM is ready
    $(document).ready(function() {
        if ($('#daily_working_hours').length > 0 || $('#working_days_per_week').length > 0) {
            initializeOrganizationValidation();
        }
    });

})(jQuery);
