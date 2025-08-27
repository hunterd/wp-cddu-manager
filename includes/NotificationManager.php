<?php
namespace CDDU_Manager;

class NotificationManager {
    
    private array $email_templates = [];
    
    public function __construct() {
        add_action('cddu_contract_created', [$this, 'notify_contract_created'], 10, 2);
        add_action('cddu_addendum_required', [$this, 'notify_addendum_required'], 10, 3);
        add_action('cddu_signature_requested', [$this, 'notify_signature_requested'], 10, 3);
        add_action('cddu_signature_completed', [$this, 'notify_signature_completed'], 10, 2);
        add_action('cddu_timesheet_submitted', [$this, 'notify_timesheet_submitted'], 10, 2);
        add_action('cddu_contract_expiring', [$this, 'notify_contract_expiring'], 10, 2);
        
        // Schedule recurring alerts
        add_action('init', [$this, 'schedule_alerts']);
        add_action('cddu_daily_alerts', [$this, 'process_daily_alerts']);
        add_action('cddu_weekly_alerts', [$this, 'process_weekly_alerts']);
        
        // Admin interface for notifications
        add_action('admin_menu', [$this, 'add_notifications_menu']);
        add_action('wp_ajax_cddu_send_test_notification', [$this, 'ajax_send_test_notification']);
        
        $this->init_email_templates();
    }

    public function schedule_alerts(): void {
        if (!wp_next_scheduled('cddu_daily_alerts')) {
            wp_schedule_event(time(), 'daily', 'cddu_daily_alerts');
        }
        
        if (!wp_next_scheduled('cddu_weekly_alerts')) {
            wp_schedule_event(time(), 'weekly', 'cddu_weekly_alerts');
        }
    }

    public function add_notifications_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_contract',
            __('Notifications', 'wp-cddu-manager'),
            __('Notifications', 'wp-cddu-manager'),
            'cddu_manage',
            'notifications',
            [$this, 'render_notifications_page']
        );
    }

    private function init_email_templates(): void {
        $this->email_templates = [
            'contract_created' => [
                'subject' => __('New CDDU Contract Created - %s', 'wp-cddu-manager'),
                'template' => 'emails/contract-created.php'
            ],
            'addendum_required' => [
                'subject' => __('Addendum Required for Contract %s', 'wp-cddu-manager'),
                'template' => 'emails/addendum-required.php'
            ],
            'signature_requested' => [
                'subject' => __('Electronic Signature Requested - %s', 'wp-cddu-manager'),
                'template' => 'emails/signature-requested.php'
            ],
            'signature_completed' => [
                'subject' => __('Document Signed Successfully - %s', 'wp-cddu-manager'),
                'template' => 'emails/signature-completed.php'
            ],
            'timesheet_submitted' => [
                'subject' => __('New Timesheet Submitted - %s', 'wp-cddu-manager'),
                'template' => 'emails/timesheet-submitted.php'
            ],
            'contract_expiring' => [
                'subject' => __('Contract Expiring Soon - %s', 'wp-cddu-manager'),
                'template' => 'emails/contract-expiring.php'
            ],
            'hours_exceeded' => [
                'subject' => __('Hours Exceeded Alert - %s', 'wp-cddu-manager'),
                'template' => 'emails/hours-exceeded.php'
            ]
        ];
    }

    public function notify_contract_created(int $contract_id, array $contract_data): void {
        $contract = get_post($contract_id);
        if (!$contract) return;

        $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
        $organization_id = $contract_data['organization_id'] ?? 0;
        
        // Notify instructor
        if ($instructor_user_id) {
            $instructor_user = get_userdata($instructor_user_id);
            if ($instructor_user && $instructor_user->user_email) {
                $this->send_notification(
                    'contract_created',
                    $instructor_user->user_email,
                    ['contract' => $contract, 'contract_data' => $contract_data, 'role' => 'instructor']
                );
            }
        }
        
        // Notify organization managers
        if ($organization_id) {
            $managers = $this->get_organization_managers($organization_id);
            foreach ($managers as $manager_email) {
                $this->send_notification(
                    'contract_created',
                    $manager_email,
                    ['contract' => $contract, 'contract_data' => $contract_data, 'role' => 'manager']
                );
            }
        }
        
        // Create notification record
        $this->create_notification_record([
            'type' => 'contract_created',
            'contract_id' => $contract_id,
            'message' => sprintf(__('Contract "%s" has been created', 'wp-cddu-manager'), $contract->post_title),
            'recipients' => array_merge(
                $instructor_email ? [$instructor_email] : [],
                $managers ?? []
            )
        ]);
    }

    public function notify_addendum_required(int $contract_id, string $reason, array $timesheet_data): void {
        $contract = get_post($contract_id);
        if (!$contract) return;

        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
        $organization_id = $contract_data['organization_id'] ?? 0;

        // Notify instructor
        if ($instructor_user_id) {
            $instructor_user = get_userdata($instructor_user_id);
            if ($instructor_user && $instructor_user->user_email) {
                $this->send_notification(
                    'addendum_required',
                    $instructor_user->user_email,
                    [
                        'contract' => $contract,
                        'contract_data' => $contract_data,
                        'reason' => $reason,
                        'timesheet_data' => $timesheet_data,
                        'role' => 'instructor'
                    ]
                );
            }
        }

        // Notify organization managers
        if ($organization_id) {
            $managers = $this->get_organization_managers($organization_id);
            foreach ($managers as $manager_email) {
                $this->send_notification(
                    'addendum_required',
                    $manager_email,
                    [
                        'contract' => $contract,
                        'contract_data' => $contract_data,
                        'reason' => $reason,
                        'timesheet_data' => $timesheet_data,
                        'role' => 'manager'
                    ]
                );
            }
        }

        $this->create_notification_record([
            'type' => 'addendum_required',
            'contract_id' => $contract_id,
            'message' => sprintf(__('Addendum required for contract "%s": %s', 'wp-cddu-manager'), $contract->post_title, $reason),
            'recipients' => array_merge(
                $instructor_email ? [$instructor_email] : [],
                $managers ?? []
            )
        ]);
    }

    public function notify_signature_requested(int $document_id, string $document_type, array $signer_data): void {
        $document = get_post($document_id);
        if (!$document) return;

        $signer_email = $signer_data['email'] ?? '';
        $signer_name = $signer_data['name'] ?? '';
        
        if ($signer_email) {
            $this->send_notification(
                'signature_requested',
                $signer_email,
                [
                    'document' => $document,
                    'document_type' => $document_type,
                    'signer_data' => $signer_data,
                    'signature_url' => $signer_data['signature_url'] ?? ''
                ]
            );
        }

        $this->create_notification_record([
            'type' => 'signature_requested',
            'document_id' => $document_id,
            'document_type' => $document_type,
            'message' => sprintf(__('Signature requested for %s "%s" from %s', 'wp-cddu-manager'), $document_type, $document->post_title, $signer_name),
            'recipients' => [$signer_email]
        ]);
    }

    public function notify_signature_completed(int $document_id, string $document_type): void {
        $document = get_post($document_id);
        if (!$document) return;

        // Get all related parties to notify
        $recipients = [];
        
        if ($document_type === 'contract') {
            $contract_data = get_post_meta($document_id, 'contract_data', true);
            $contract_data = maybe_unserialize($contract_data);
            
            $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
            $organization_id = $contract_data['organization_id'] ?? 0;
            
            if ($instructor_user_id) {
                $instructor_user = get_userdata($instructor_user_id);
                if ($instructor_user && $instructor_user->user_email) {
                    $recipients[] = $instructor_user->user_email;
                }
            }
            
            if ($organization_id) {
                $managers = $this->get_organization_managers($organization_id);
                $recipients = array_merge($recipients, $managers);
            }
        } elseif ($document_type === 'addendum') {
            $parent_contract_id = get_post_meta($document_id, 'parent_contract_id', true);
            if ($parent_contract_id) {
                $contract_data = get_post_meta($parent_contract_id, 'contract_data', true);
                $contract_data = maybe_unserialize($contract_data);
                
                $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
                $organization_id = $contract_data['organization_id'] ?? 0;
                
                if ($instructor_user_id) {
                    $instructor_user = get_userdata($instructor_user_id);
                    if ($instructor_user && $instructor_user->user_email) {
                        $recipients[] = $instructor_user->user_email;
                    }
                }
                
                if ($organization_id) {
                    $managers = $this->get_organization_managers($organization_id);
                    $recipients = array_merge($recipients, $managers);
                }
            }
        }

        foreach (array_unique($recipients) as $email) {
            $this->send_notification(
                'signature_completed',
                $email,
                [
                    'document' => $document,
                    'document_type' => $document_type
                ]
            );
        }

        $this->create_notification_record([
            'type' => 'signature_completed',
            'document_id' => $document_id,
            'document_type' => $document_type,
            'message' => sprintf(__('%s "%s" has been signed by all parties', 'wp-cddu-manager'), ucfirst($document_type), $document->post_title),
            'recipients' => array_unique($recipients)
        ]);
    }

    public function notify_timesheet_submitted(int $timesheet_id, array $timesheet_data): void {
        $timesheet = get_post($timesheet_id);
        if (!$timesheet) return;

        $contract_id = $timesheet_data['contract_id'] ?? 0;
        if (!$contract_id) return;

        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $organization_id = $contract_data['organization_id'] ?? 0;
        if ($organization_id) {
            $managers = $this->get_organization_managers($organization_id);
            foreach ($managers as $manager_email) {
                $this->send_notification(
                    'timesheet_submitted',
                    $manager_email,
                    [
                        'timesheet' => $timesheet,
                        'timesheet_data' => $timesheet_data,
                        'contract_id' => $contract_id
                    ]
                );
            }
        }

        $this->create_notification_record([
            'type' => 'timesheet_submitted',
            'timesheet_id' => $timesheet_id,
            'contract_id' => $contract_id,
            'message' => sprintf(__('Timesheet submitted for %s %s', 'wp-cddu-manager'), $timesheet_data['month'] ?? '', $timesheet_data['year'] ?? ''),
            'recipients' => $managers ?? []
        ]);
    }

    public function notify_contract_expiring(int $contract_id, int $days_until_expiry): void {
        $contract = get_post($contract_id);
        if (!$contract) return;

        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
        $organization_id = $contract_data['organization_id'] ?? 0;
        
        $recipients = [];
        
        if ($instructor_user_id) {
            $instructor_user = get_userdata($instructor_user_id);
            if ($instructor_user && $instructor_user->user_email) {
                $recipients[] = $instructor_user->user_email;
            }
        }
        
        if ($organization_id) {
            $managers = $this->get_organization_managers($organization_id);
            $recipients = array_merge($recipients, $managers);
        }

        foreach (array_unique($recipients) as $email) {
            $this->send_notification(
                'contract_expiring',
                $email,
                [
                    'contract' => $contract,
                    'contract_data' => $contract_data,
                    'days_until_expiry' => $days_until_expiry
                ]
            );
        }

        $this->create_notification_record([
            'type' => 'contract_expiring',
            'contract_id' => $contract_id,
            'message' => sprintf(__('Contract "%s" expires in %d days', 'wp-cddu-manager'), $contract->post_title, $days_until_expiry),
            'recipients' => array_unique($recipients)
        ]);
    }

    public function process_daily_alerts(): void {
        // Check for contracts expiring in 30, 15, 7, and 1 days
        $warning_days = [30, 15, 7, 1];
        
        foreach ($warning_days as $days) {
            $this->check_expiring_contracts($days);
        }
        
        // Check for overdue timesheets
        $this->check_overdue_timesheets();
    }

    public function process_weekly_alerts(): void {
        // Generate weekly summary for managers
        $this->send_weekly_summary();
    }

    private function check_expiring_contracts(int $days): void {
        $target_date = date('Y-m-d', strtotime("+{$days} days"));
        
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'contract_data',
                    'value' => $target_date,
                    'compare' => 'LIKE'
                ]
            ],
            'numberposts' => -1
        ]);

        foreach ($contracts as $contract) {
            $contract_data = get_post_meta($contract->ID, 'contract_data', true);
            $contract_data = maybe_unserialize($contract_data);
            
            $end_date = $contract_data['end_date'] ?? '';
            if ($end_date === $target_date) {
                // Check if we haven't already sent this warning
                $warning_sent = get_post_meta($contract->ID, "expiry_warning_{$days}_days", true);
                if (!$warning_sent) {
                    $this->notify_contract_expiring($contract->ID, $days);
                    update_post_meta($contract->ID, "expiry_warning_{$days}_days", true);
                }
            }
        }
    }

    private function check_overdue_timesheets(): void {
        // Get contracts where timesheets should have been submitted but haven't been
        $current_month = date('n');
        $current_year = date('Y');
        $last_month = $current_month === 1 ? 12 : $current_month - 1;
        $last_month_year = $current_month === 1 ? $current_year - 1 : $current_year;

        $active_contracts = get_posts([
            'post_type' => 'cddu_contract',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);

        foreach ($active_contracts as $contract) {
            $contract_data = get_post_meta($contract->ID, 'contract_data', true);
            $contract_data = maybe_unserialize($contract_data);
            
            $start_date = new \DateTime($contract_data['start_date'] ?? '');
            $end_date = new \DateTime($contract_data['end_date'] ?? '');
            $last_month_date = new \DateTime("{$last_month_year}-{$last_month}-01");
            
            // Check if contract was active last month
            if ($start_date <= $last_month_date && $end_date >= $last_month_date) {
                // Check if timesheet exists for last month
                $timesheet = get_posts([
                    'post_type' => 'cddu_timesheet',
                    'meta_query' => [
                        [
                            'key' => 'contract_id',
                            'value' => $contract->ID,
                            'compare' => '='
                        ],
                        [
                            'key' => 'month',
                            'value' => $last_month,
                            'compare' => '='
                        ],
                        [
                            'key' => 'year',
                            'value' => $last_month_year,
                            'compare' => '='
                        ]
                    ],
                    'numberposts' => 1
                ]);

                if (empty($timesheet)) {
                    // Send overdue notification
                    $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
                    if ($instructor_user_id) {
                        $instructor_user = get_userdata($instructor_user_id);
                        if ($instructor_user && $instructor_user->user_email) {
                            $this->send_notification(
                                'timesheet_overdue',
                                $instructor_user->user_email,
                                [
                                    'contract' => $contract,
                                    'month' => $last_month,
                                    'year' => $last_month_year
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    private function send_weekly_summary(): void {
        // Get all organization managers
        $organizations = get_posts([
            'post_type' => 'cddu_organization',
            'numberposts' => -1
        ]);

        foreach ($organizations as $organization) {
            $managers = $this->get_organization_managers($organization->ID);
            $summary_data = $this->generate_weekly_summary($organization->ID);
            
            foreach ($managers as $manager_email) {
                $this->send_notification(
                    'weekly_summary',
                    $manager_email,
                    [
                        'organization' => $organization,
                        'summary_data' => $summary_data
                    ]
                );
            }
        }
    }

    private function generate_weekly_summary(int $organization_id): array {
        $week_start = date('Y-m-d', strtotime('last Monday'));
        $week_end = date('Y-m-d', strtotime('next Sunday'));

        // Get contracts for this organization
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'meta_query' => [
                [
                    'key' => 'contract_data',
                    'value' => '"organization_id";i:' . $organization_id,
                    'compare' => 'LIKE'
                ]
            ],
            'numberposts' => -1
        ]);

        $contract_ids = array_map(function($contract) { return $contract->ID; }, $contracts);

        // Get timesheets submitted this week
        $timesheets = get_posts([
            'post_type' => 'cddu_timesheet',
            'date_query' => [
                [
                    'after' => $week_start,
                    'before' => $week_end,
                    'inclusive' => true
                ]
            ],
            'meta_query' => [
                [
                    'key' => 'contract_id',
                    'value' => $contract_ids,
                    'compare' => 'IN'
                ]
            ],
            'numberposts' => -1
        ]);

        // Get addendums created this week
        $addendums = get_posts([
            'post_type' => 'cddu_addendum',
            'date_query' => [
                [
                    'after' => $week_start,
                    'before' => $week_end,
                    'inclusive' => true
                ]
            ],
            'meta_query' => [
                [
                    'key' => 'parent_contract_id',
                    'value' => $contract_ids,
                    'compare' => 'IN'
                ]
            ],
            'numberposts' => -1
        ]);

        return [
            'week_start' => $week_start,
            'week_end' => $week_end,
            'total_contracts' => count($contracts),
            'timesheets_submitted' => count($timesheets),
            'addendums_created' => count($addendums),
            'contracts' => $contracts,
            'timesheets' => $timesheets,
            'addendums' => $addendums
        ];
    }

    private function send_notification(string $type, string $email, array $data): bool {
        if (!isset($this->email_templates[$type])) {
            return false;
        }

        $template = $this->email_templates[$type];
        $subject = sprintf($template['subject'], $data['contract']->post_title ?? $data['document']->post_title ?? '');
        
        $message = $this->render_email_template($template['template'], $data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option('admin_email')
        ];

        return wp_mail($email, $subject, $message, $headers);
    }

    private function render_email_template(string $template_name, array $data): string {
        $template_path = plugin_dir_path(__FILE__) . '../templates/' . $template_name;
        
        if (!file_exists($template_path)) {
            // Fallback to simple text template
            return $this->generate_simple_email($data);
        }

        ob_start();
        extract($data);
        include $template_path;
        return ob_get_clean();
    }

    private function generate_simple_email(array $data): string {
        $message = '<html><body>';
        $message .= '<h2>' . __('CDDU Notification', 'wp-cddu-manager') . '</h2>';
        
        if (isset($data['contract'])) {
            $message .= '<p><strong>' . __('Contract:', 'wp-cddu-manager') . '</strong> ' . esc_html($data['contract']->post_title) . '</p>';
        }
        
        if (isset($data['message'])) {
            $message .= '<p>' . esc_html($data['message']) . '</p>';
        }
        
        $message .= '<p>' . __('Please log in to your dashboard for more details.', 'wp-cddu-manager') . '</p>';
        $message .= '</body></html>';
        
        return $message;
    }

    private function get_organization_managers(int $organization_id): array {
        $organization = get_post($organization_id);
        if (!$organization) return [];

        $organization_data = get_post_meta($organization_id, 'organization', true);
        $organization_data = maybe_unserialize($organization_data);
        
        $managers = [];
        
        // Get primary contact email
        if (!empty($organization_data['contact_email'])) {
            $managers[] = $organization_data['contact_email'];
        }
        
        // Get additional manager emails
        if (!empty($organization_data['manager_emails'])) {
            $additional_emails = explode(',', $organization_data['manager_emails']);
            $managers = array_merge($managers, array_map('trim', $additional_emails));
        }
        
        return array_filter(array_unique($managers));
    }

    private function create_notification_record(array $data): int {
        return wp_insert_post([
            'post_type' => 'cddu_notification',
            'post_title' => $data['message'],
            'post_content' => maybe_serialize([
                'type' => $data['type'],
                'recipients' => $data['recipients'],
                'contract_id' => $data['contract_id'] ?? null,
                'document_id' => $data['document_id'] ?? null,
                'document_type' => $data['document_type'] ?? null,
                'timesheet_id' => $data['timesheet_id'] ?? null,
                'sent_date' => current_time('mysql')
            ]),
            'post_status' => 'publish'
        ]);
    }

    public function render_notifications_page(): void {
        $recent_notifications = get_posts([
            'post_type' => 'cddu_notification',
            'numberposts' => 50,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Notifications Management', 'wp-cddu-manager'); ?></h1>
            
            <div class="notification-controls">
                <h2><?php echo esc_html__('Test Notifications', 'wp-cddu-manager'); ?></h2>
                <p>
                    <input type="email" id="test-email" placeholder="<?php echo esc_attr__('Test email address', 'wp-cddu-manager'); ?>">
                    <select id="test-template">
                        <option value=""><?php echo esc_html__('Select template', 'wp-cddu-manager'); ?></option>
                        <?php foreach ($this->email_templates as $key => $template): ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($template['subject']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="send-test" class="button">
                        <?php echo esc_html__('Send Test Email', 'wp-cddu-manager'); ?>
                    </button>
                </p>
            </div>

            <div class="notification-history">
                <h2><?php echo esc_html__('Recent Notifications', 'wp-cddu-manager'); ?></h2>
                
                <?php if (empty($recent_notifications)): ?>
                    <p><?php echo esc_html__('No notifications sent yet.', 'wp-cddu-manager'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Date', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Type', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Message', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Recipients', 'wp-cddu-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_notifications as $notification): ?>
                                <?php
                                $notification_data = maybe_unserialize($notification->post_content);
                                $recipients = $notification_data['recipients'] ?? [];
                                ?>
                                <tr>
                                    <td><?php echo esc_html(get_the_date('d/m/Y H:i', $notification)); ?></td>
                                    <td><?php echo esc_html($notification_data['type'] ?? ''); ?></td>
                                    <td><?php echo esc_html($notification->post_title); ?></td>
                                    <td><?php echo esc_html(implode(', ', array_slice($recipients, 0, 3))); ?><?php if (count($recipients) > 3) echo esc_html(sprintf(__(' +%d more', 'wp-cddu-manager'), count($recipients) - 3)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.getElementById('send-test').addEventListener('click', function() {
            const email = document.getElementById('test-email').value;
            const template = document.getElementById('test-template').value;
            
            if (!email || !template) {
                alert('<?php echo esc_js(__('Please fill in all fields', 'wp-cddu-manager')); ?>');
                return;
            }
            
            const data = new FormData();
            data.append('action', 'cddu_send_test_notification');
            data.append('email', email);
            data.append('template', template);
            data.append('nonce', '<?php echo wp_create_nonce('cddu_test_notification'); ?>');
            
            fetch(ajaxurl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('<?php echo esc_js(__('Test email sent successfully', 'wp-cddu-manager')); ?>');
                } else {
                    alert('<?php echo esc_js(__('Error sending test email', 'wp-cddu-manager')); ?>');
                }
            });
        });
        </script>
        <?php
    }

    public function ajax_send_test_notification(): void {
        check_ajax_referer('cddu_test_notification', 'nonce');
        
        if (!current_user_can('cddu_manage')) {
            wp_send_json_error(['message' => __('Access denied', 'wp-cddu-manager')]);
        }
        
        $email = sanitize_email($_POST['email'] ?? '');
        $template = sanitize_text_field($_POST['template'] ?? '');
        
        if (!$email || !$template) {
            wp_send_json_error(['message' => __('Invalid parameters', 'wp-cddu-manager')]);
        }
        
        // Create mock data for testing
        $mock_data = $this->create_mock_notification_data($template);
        
        $result = $this->send_notification($template, $email, $mock_data);
        
        if ($result) {
            wp_send_json_success(['message' => __('Test email sent successfully', 'wp-cddu-manager')]);
        } else {
            wp_send_json_error(['message' => __('Failed to send test email', 'wp-cddu-manager')]);
        }
    }

    private function create_mock_notification_data(string $template): array {
        // Create mock post object
        $mock_contract = (object) [
            'ID' => 1,
            'post_title' => __('Test Contract - Web Development Training', 'wp-cddu-manager'),
            'post_date' => current_time('mysql')
        ];

        $mock_contract_data = [
            'instructor_id' => 1,
            'organization_id' => 1,
            'action' => __('Web Development Training', 'wp-cddu-manager'),
            'location' => __('Training Center A', 'wp-cddu-manager'),
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+6 months')),
            'annual_hours' => 150,
            'hourly_rate' => 45
        ];

        return [
            'contract' => $mock_contract,
            'contract_data' => $mock_contract_data,
            'document' => $mock_contract,
            'document_type' => 'contract',
            'role' => 'instructor',
            'message' => __('This is a test notification', 'wp-cddu-manager'),
            'days_until_expiry' => 30
        ];
    }
}
