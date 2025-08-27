<?php
namespace CDDU_Manager\Admin;

class HolidayCalendarManager {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Register holiday calendar post type
     */
    public function register_post_type(): void {
        register_post_type('cddu_holiday', [
            'label' => __('Holiday Calendars', 'wp-cddu-manager'),
            'labels' => [
                'name' => __('Holiday Calendars', 'wp-cddu-manager'),
                'singular_name' => __('Holiday Calendar', 'wp-cddu-manager'),
                'add_new' => __('Add New Holiday Calendar', 'wp-cddu-manager'),
                'add_new_item' => __('Add New Holiday Calendar', 'wp-cddu-manager'),
                'edit_item' => __('Edit Holiday Calendar', 'wp-cddu-manager'),
                'new_item' => __('New Holiday Calendar', 'wp-cddu-manager'),
                'view_item' => __('View Holiday Calendar', 'wp-cddu-manager'),
                'search_items' => __('Search Holiday Calendars', 'wp-cddu-manager'),
                'not_found' => __('No holiday calendars found', 'wp-cddu-manager'),
                'not_found_in_trash' => __('No holiday calendars found in trash', 'wp-cddu-manager'),
                'all_items' => __('All Holiday Calendars', 'wp-cddu-manager'),
                'menu_name' => __('Holiday Calendars', 'wp-cddu-manager'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title'],
            'capability_type' => 'post',
            'menu_position' => 26,
        ]);
    }

    /**
     * Add holiday calendar metaboxes
     */
    public function add_metaboxes(): void {
        add_meta_box(
            'cddu_holiday_details', 
            __('Holiday Calendar Details', 'wp-cddu-manager'), 
            [$this, 'render_holiday_calendar_metabox'], 
            'cddu_holiday', 
            'normal', 
            'high'
        );

        add_meta_box(
            'cddu_holiday_organizations', 
            __('Assigned Organizations', 'wp-cddu-manager'), 
            [$this, 'render_organizations_metabox'], 
            'cddu_holiday', 
            'side', 
            'default'
        );
    }

    /**
     * Enqueue scripts for holiday calendar management
     */
    public function enqueue_scripts($hook): void {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        global $post_type;
        if ($post_type !== 'cddu_holiday') {
            return;
        }

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-datepicker', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css');
    }

    /**
     * Render holiday calendar metabox
     */
    public function render_holiday_calendar_metabox(\WP_Post $post): void {
        $holidays = get_post_meta($post->ID, 'holidays', true);
        if (!is_array($holidays)) {
            $holidays = [];
        }

        // Add nonce for security
        wp_nonce_field('cddu_holiday_nonce', 'cddu_holiday_nonce');

        include CDDU_MNGR_PATH . 'templates/partials/admin/holiday-calendar-metabox.php';
    }

    /**
     * Render organizations metabox
     */
    public function render_organizations_metabox(\WP_Post $post): void {
        $assigned_organizations = get_post_meta($post->ID, 'assigned_organizations', true);
        if (!is_array($assigned_organizations)) {
            $assigned_organizations = [];
        }

        // Get all organizations
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        include CDDU_MNGR_PATH . 'templates/partials/admin/holiday-calendar-organizations-metabox.php';
    }

    /**
     * Save holiday calendar meta data
     */
    public function save_holiday_calendar_meta(int $post_id, \WP_Post $post): void {
        // Check if user has permission to edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['cddu_holiday_nonce']) || 
            !wp_verify_nonce($_POST['cddu_holiday_nonce'], 'cddu_holiday_nonce')) {
            return;
        }

        // Don't save on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save holidays
        if (isset($_POST['holidays'])) {
            $holidays = [];
            foreach ($_POST['holidays'] as $holiday) {
                if (!empty($holiday['date']) && !empty($holiday['name'])) {
                    $holidays[] = [
                        'date' => sanitize_text_field($holiday['date']),
                        'name' => sanitize_text_field($holiday['name']),
                        'type' => sanitize_text_field($holiday['type'] ?? 'public'),
                        'recurring' => isset($holiday['recurring']) ? 1 : 0
                    ];
                }
            }
            update_post_meta($post_id, 'holidays', $holidays);
        } else {
            delete_post_meta($post_id, 'holidays');
        }

        // Save assigned organizations
        if (isset($_POST['assigned_organizations'])) {
            $assigned_organizations = array_map('intval', $_POST['assigned_organizations']);
            update_post_meta($post_id, 'assigned_organizations', $assigned_organizations);
        } else {
            delete_post_meta($post_id, 'assigned_organizations');
        }
    }

    /**
     * Get holidays for a specific organization
     */
    public static function get_organization_holidays(int $organization_id): array {
        // Find holiday calendars assigned to this organization
        $holiday_calendars = get_posts([
            'post_type' => 'cddu_holiday',
            'meta_query' => [
                [
                    'key' => 'assigned_organizations',
                    'value' => serialize(strval($organization_id)),
                    'compare' => 'LIKE'
                ]
            ],
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $all_holidays = [];
        foreach ($holiday_calendars as $calendar) {
            $holidays = get_post_meta($calendar->ID, 'holidays', true);
            if (is_array($holidays)) {
                $all_holidays = array_merge($all_holidays, $holidays);
            }
        }

        return $all_holidays;
    }

    /**
     * Check if a specific date is a holiday for an organization
     */
    public static function is_holiday(int $organization_id, string $date): bool {
        $holidays = self::get_organization_holidays($organization_id);
        $check_date = new \DateTimeImmutable($date);
        
        foreach ($holidays as $holiday) {
            $holiday_date = new \DateTimeImmutable($holiday['date']);
            
            if ($holiday['recurring']) {
                // For recurring holidays, only check month and day
                if ($holiday_date->format('m-d') === $check_date->format('m-d')) {
                    return true;
                }
            } else {
                // For non-recurring holidays, check exact date
                if ($holiday_date->format('Y-m-d') === $check_date->format('Y-m-d')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Calculate working days between two dates, excluding holidays
     */
    public static function calculate_working_days(int $organization_id, string $start_date, string $end_date, int $working_days_per_week = 5): int {
        $start = new \DateTimeImmutable($start_date);
        $end = new \DateTimeImmutable($end_date);
        
        $working_days = 0;
        $current = $start;
        
        // Define which days are working days (1 = Monday, 7 = Sunday)
        $working_day_numbers = [];
        for ($i = 1; $i <= $working_days_per_week; $i++) {
            $working_day_numbers[] = $i;
        }
        
        while ($current <= $end) {
            // Check if it's a working day (Monday to Friday by default, or according to working_days_per_week)
            $day_of_week = (int) $current->format('N');
            
            if (in_array($day_of_week, $working_day_numbers) && 
                !self::is_holiday($organization_id, $current->format('Y-m-d'))) {
                $working_days++;
            }
            
            $current = $current->add(new \DateInterval('P1D'));
        }
        
        return $working_days;
    }
}
