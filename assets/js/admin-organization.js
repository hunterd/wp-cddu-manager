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
                if (!confirm('This instructor has active contracts. Are you sure you want to unassign them? This may affect ongoing contracts.')) {
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
                    ? `Showing all ${totalAll} ${type}s`
                    : `Showing ${totalVisible} of ${totalAll} ${type}s`
            );
        }
        
        // Update selected count
        const selectedCount = $selectedItems.length;
        const visibleSelectedCount = $visibleSelectedItems.length;
        const $selectedCount = $(`#${type}-selected-count`);
        if ($selectedCount.length) {
            $selectedCount.text(
                selectedCount === visibleSelectedCount
                    ? `${selectedCount} selected`
                    : `${selectedCount} selected (${visibleSelectedCount} visible)`
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
        return text; // In a real implementation, this would handle translations
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
})(jQuery);
