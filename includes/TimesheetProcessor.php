<?php
namespace CDDU_Manager;

use CDDU_Manager\Calculations;

class TimesheetProcessor {
    
    public function __construct() {
        // Hook into timesheet creation/update to check for addendum needs
        add_action('save_post_cddu_timesheet', [$this, 'process_timesheet'], 10, 2);
        add_action('transition_post_status', [$this, 'on_timesheet_status_change'], 10, 3);
    }

    /**
     * Process timesheet when saved - check if addendum is needed
     */
    public function process_timesheet(int $timesheet_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $status = get_post_meta($timesheet_id, 'status', true);
        if ($status !== 'submitted') {
            return;
        }

        $contract_id = get_post_meta($timesheet_id, 'contract_id', true);
        $hours_worked = floatval(get_post_meta($timesheet_id, 'hours_worked', true));
        $month = get_post_meta($timesheet_id, 'month', true);
        $year = intval(get_post_meta($timesheet_id, 'year', true));

        if (!$contract_id || !$hours_worked) {
            return;
        }

        // Check if hours exceed planned hours for this period
        $needs_addendum = $this->check_addendum_needed($contract_id, $timesheet_id, $hours_worked, $month, $year);
        
        if ($needs_addendum) {
            $this->create_addendum_notification($contract_id, $timesheet_id, $needs_addendum);
        }
    }

    /**
     * Check if an addendum is needed based on timesheet hours vs planned hours
     */
    private function check_addendum_needed(int $contract_id, int $timesheet_id, float $hours_worked, string $month, int $year): array|false {
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        if (!$contract_data) {
            return false;
        }

        $annual_hours = floatval($contract_data['annual_hours'] ?? 0);
        $start_date = new \DateTime($contract_data['start_date'] ?? 'now');
        $end_date = new \DateTime($contract_data['end_date'] ?? 'now');
        
        // Calculate planned hours for this month
        $month_start = new \DateTime("$year-" . date('m', strtotime($month)) . "-01");
        $month_end = clone $month_start;
        $month_end->modify('last day of this month');
        
        // Adjust for contract period
        $period_start = max($start_date, $month_start);
        $period_end = min($end_date, $month_end);
        
        if ($period_start > $period_end) {
            return false; // No overlap with contract period
        }
        
        // Calculate expected hours for this period
        $total_contract_days = $start_date->diff($end_date)->days + 1;
        $month_contract_days = $period_start->diff($period_end)->days + 1;
        $expected_monthly_hours = ($annual_hours / $total_contract_days) * $month_contract_days;
        
        // Allow 10% tolerance
        $tolerance = $expected_monthly_hours * 0.1;
        $hours_difference = $hours_worked - $expected_monthly_hours;
        
        if ($hours_difference > $tolerance) {
            return [
                'type' => 'hours_exceeded',
                'expected_hours' => $expected_monthly_hours,
                'actual_hours' => $hours_worked,
                'excess_hours' => $hours_difference,
                'month' => $month,
                'year' => $year
            ];
        }
        
        // Check if total worked hours exceed annual contract hours
        $total_worked_hours = $this->get_total_worked_hours($contract_id);
        if ($total_worked_hours > $annual_hours) {
            return [
                'type' => 'annual_exceeded',
                'annual_hours' => $annual_hours,
                'total_worked' => $total_worked_hours,
                'excess_hours' => $total_worked_hours - $annual_hours
            ];
        }
        
        return false;
    }

    /**
     * Get total worked hours for a contract across all timesheets
     */
    private function get_total_worked_hours(int $contract_id): float {
        $timesheets = get_posts([
            'post_type' => 'cddu_timesheet',
            'meta_query' => [
                [
                    'key' => 'contract_id',
                    'value' => $contract_id,
                    'compare' => '='
                ],
                [
                    'key' => 'status',
                    'value' => 'submitted',
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        $total_hours = 0;
        foreach ($timesheets as $timesheet) {
            $hours = floatval(get_post_meta($timesheet->ID, 'hours_worked', true));
            $total_hours += $hours;
        }

        return $total_hours;
    }

    /**
     * Create notification for organization about addendum need
     */
    private function create_addendum_notification(int $contract_id, int $timesheet_id, array $addendum_data): void {
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $instructor_id = $contract_data['instructor_id'] ?? 0;
        $organization_id = $contract_data['organization_id'] ?? 0;

        // Create notification post
        $notification_id = wp_insert_post([
            'post_type' => 'cddu_notification',
            'post_title' => sprintf(
                __('Addendum Required - Contract %s', 'wp-cddu-manager'),
                get_the_title($contract_id)
            ),
            'post_content' => $this->generate_addendum_notification_content($addendum_data),
            'post_status' => 'publish',
            'meta_input' => [
                'notification_type' => 'addendum_required',
                'contract_id' => $contract_id,
                'timesheet_id' => $timesheet_id,
                'instructor_id' => $instructor_id,
                'organization_id' => $organization_id,
                'addendum_data' => maybe_serialize($addendum_data),
                'status' => 'pending',
                'created_date' => current_time('mysql')
            ]
        ]);

        // Send email notification to organization managers
        $this->send_addendum_notification_email($organization_id, $notification_id, $addendum_data);
        
        // Mark timesheet as requiring addendum
        update_post_meta($timesheet_id, 'requires_addendum', true);
        update_post_meta($timesheet_id, 'addendum_notification_id', $notification_id);
    }

    /**
     * Generate notification content based on addendum data
     */
    private function generate_addendum_notification_content(array $data): string {
        $content = '';
        
        if ($data['type'] === 'hours_exceeded') {
            $content = sprintf(
                __('The instructor has worked %s hours in %s %d, exceeding the expected %s hours by %s hours. An addendum may be required to adjust the contract terms.', 'wp-cddu-manager'),
                number_format($data['actual_hours'], 2),
                $data['month'],
                $data['year'],
                number_format($data['expected_hours'], 2),
                number_format($data['excess_hours'], 2)
            );
        } elseif ($data['type'] === 'annual_exceeded') {
            $content = sprintf(
                __('The instructor has worked a total of %s hours, exceeding the annual contract limit of %s hours by %s hours. An addendum is required to adjust the contract terms.', 'wp-cddu-manager'),
                number_format($data['total_worked'], 2),
                number_format($data['annual_hours'], 2),
                number_format($data['excess_hours'], 2)
            );
        }
        
        return $content;
    }

    /**
     * Send email notification to organization managers
     */
    private function send_addendum_notification_email(int $organization_id, int $notification_id, array $addendum_data): void {
        $managers = get_post_meta($organization_id, 'organization_managers', true);
        $managers = maybe_unserialize($managers) ?: [];

        if (empty($managers)) {
            return;
        }

        $subject = sprintf(
            __('[%s] Addendum Required - Hours Exceeded', 'wp-cddu-manager'),
            get_bloginfo('name')
        );

        $message = $this->generate_addendum_notification_content($addendum_data);
        $message .= "\n\n" . sprintf(
            __('Please review this notification in the admin panel: %s', 'wp-cddu-manager'),
            admin_url('edit.php?post_type=cddu_notification')
        );

        foreach ($managers as $manager_id) {
            $user = get_user_by('ID', $manager_id);
            if ($user) {
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }

    /**
     * Handle timesheet status changes
     */
    public function on_timesheet_status_change(string $new_status, string $old_status, \WP_Post $post): void {
        if ($post->post_type !== 'cddu_timesheet') {
            return;
        }

        $meta_status = get_post_meta($post->ID, 'status', true);
        
        // If timesheet is approved and requires addendum, suggest creating one
        if ($meta_status === 'approved' && get_post_meta($post->ID, 'requires_addendum', true)) {
            $this->suggest_addendum_creation($post->ID);
        }
    }

    /**
     * Suggest creating an addendum for approved excessive hours
     */
    private function suggest_addendum_creation(int $timesheet_id): void {
        $contract_id = get_post_meta($timesheet_id, 'contract_id', true);
        $notification_id = get_post_meta($timesheet_id, 'addendum_notification_id', true);
        
        if (!$contract_id || !$notification_id) {
            return;
        }

        // Update notification to suggest addendum creation
        update_post_meta($notification_id, 'status', 'approved_for_addendum');
        update_post_meta($notification_id, 'approved_date', current_time('mysql'));
        
        // This could trigger automatic addendum creation or send another notification
        do_action('cddu_addendum_approved', $contract_id, $timesheet_id, $notification_id);
    }

    /**
     * Create addendum automatically from approved notification
     */
    public function create_addendum_from_notification(int $contract_id, int $timesheet_id, int $notification_id): int {
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $addendum_data = get_post_meta($notification_id, 'addendum_data', true);
        $addendum_data = maybe_unserialize($addendum_data);
        
        $timesheet_hours = floatval(get_post_meta($timesheet_id, 'hours_worked', true));
        $month = get_post_meta($timesheet_id, 'month', true);
        $year = get_post_meta($timesheet_id, 'year', true);

        // Calculate new totals
        $additional_hours = $addendum_data['excess_hours'] ?? 0;
        $new_annual_hours = $contract_data['annual_hours'] + $additional_hours;
        $hourly_rate = $contract_data['hourly_rate'] ?? 0;
        
        // Recalculate with new hours
        $new_calculations = Calculations::calculate_contract_values([
            'annual_hours' => $new_annual_hours,
            'hourly_rate' => $hourly_rate,
            'start_date' => $contract_data['start_date'],
            'end_date' => $contract_data['end_date']
        ]);

        $addendum_details = [
            'reason' => 'hours_exceeded',
            'original_annual_hours' => $contract_data['annual_hours'],
            'additional_hours' => $additional_hours,
            'new_annual_hours' => $new_annual_hours,
            'original_calculations' => get_post_meta($contract_id, 'calculations', true),
            'new_calculations' => $new_calculations,
            'effective_month' => $month,
            'effective_year' => $year,
            'timesheet_id' => $timesheet_id
        ];

        // Create addendum post
        $addendum_id = wp_insert_post([
            'post_type' => 'cddu_addendum',
            'post_title' => sprintf(
                __('Addendum %s - Additional Hours %s %d', 'wp-cddu-manager'),
                get_the_title($contract_id),
                $month,
                $year
            ),
            'post_status' => 'draft',
            'meta_input' => [
                'parent_contract_id' => $contract_id,
                'addendum_data' => maybe_serialize($addendum_details),
                'notification_id' => $notification_id,
                'status' => 'draft',
                'created_date' => current_time('mysql'),
                'addendum_type' => 'hours_adjustment'
            ]
        ]);

        // Update notification
        update_post_meta($notification_id, 'addendum_id', $addendum_id);
        update_post_meta($notification_id, 'status', 'addendum_created');

        return $addendum_id;
    }
}
