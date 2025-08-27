/**
 * CDDU Manager Notification System
 * Provides clean, inline notifications instead of browser alerts
 */
window.CDDUNotifications = {
    /**
     * Show a notification message
     * @param {string} message - The message to display
     * @param {string} type - Type of notification: 'success', 'error', 'warning', 'info'
     * @param {string|null} containerId - ID of container to append to, or null for floating
     * @param {number} duration - Auto-hide duration in milliseconds (0 = no auto-hide)
     */
    show: function(message, type = 'info', containerId = null, duration = 5000) {
        const notificationId = 'cddu-notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        
        const notification = jQuery('<div>', {
            id: notificationId,
            class: 'cddu-notification cddu-notification-' + type,
            html: message,
            css: {
                display: 'none'
            }
        });

        // Add to specified container or create floating notification
        if (containerId && jQuery('#' + containerId).length) {
            jQuery('#' + containerId).append(notification);
        } else {
            // Create floating notification container if it doesn't exist
            if (!jQuery('#cddu-floating-notifications').length) {
                jQuery('body').append('<div id="cddu-floating-notifications"></div>');
            }
            jQuery('#cddu-floating-notifications').append(notification);
        }

        // Show with animation
        notification.fadeIn(300);

        // Auto-hide if duration is specified
        if (duration > 0) {
            setTimeout(function() {
                CDDUNotifications.hide(notificationId);
            }, duration);
        }

        return notificationId;
    },

    /**
     * Hide a specific notification
     * @param {string} notificationId - ID of notification to hide
     */
    hide: function(notificationId) {
        jQuery('#' + notificationId).fadeOut(300, function() {
            jQuery(this).remove();
        });
    },

    /**
     * Hide all notifications in a container
     * @param {string|null} containerId - Container ID, or null for all floating notifications
     */
    hideAll: function(containerId = null) {
        const selector = containerId ? '#' + containerId + ' .cddu-notification' : '#cddu-floating-notifications .cddu-notification';
        jQuery(selector).fadeOut(300, function() {
            jQuery(this).remove();
        });
    },

    /**
     * Create an inline notification container for a form field
     * @param {string} fieldId - ID of the field to create container for
     * @param {string} position - 'after' or 'before' the field
     */
    createInlineContainer: function(fieldId, position = 'after') {
        const containerId = fieldId + '-notifications';
        if (!jQuery('#' + containerId).length) {
            const container = jQuery('<div>', {
                id: containerId,
                class: 'cddu-inline-notifications',
                css: {
                    marginTop: position === 'after' ? '5px' : '0',
                    marginBottom: position === 'before' ? '5px' : '0'
                }
            });

            if (position === 'after') {
                jQuery('#' + fieldId).after(container);
            } else {
                jQuery('#' + fieldId).before(container);
            }
        }
        return containerId;
    }
};
