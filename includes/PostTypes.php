<?php
namespace CDDU_Manager;

use CDDU_Manager\Admin\ContractManager;
use CDDU_Manager\Admin\MissionManager;
use CDDU_Manager\Admin\LearnerManager;
use CDDU_Manager\Admin\OrganizationManager;
use CDDU_Manager\Admin\NotificationManager;
use CDDU_Manager\Admin\SignatureManager;
use CDDU_Manager\Admin\InstructorManager;
use CDDU_Manager\Admin\HolidayCalendarManager;

class PostTypes {
    private $contractManager;
    private $missionManager;
    private $learnerManager;
    private $organizationManager;
    private $notificationManager;
    private $signatureManager;
    private $instructorManager;
    private $holidayCalendarManager;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // Initialize managers
        $this->contractManager = new ContractManager();
        $this->missionManager = new MissionManager();
        $this->learnerManager = new LearnerManager();
        $this->organizationManager = new OrganizationManager();
        $this->notificationManager = new NotificationManager();
        $this->signatureManager = new SignatureManager();
        $this->instructorManager = new InstructorManager();
        $this->holidayCalendarManager = new HolidayCalendarManager();
    }

    public function enqueue_admin_styles($hook): void {
        // Only load on organization edit pages
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        global $post_type;
        if (!in_array($post_type, ['cddu_organization', 'cddu_contract', 'cddu_mission', 'cddu_learner', 'cddu_notification', 'cddu_signature', 'cddu_holiday'])) {
            return;
        }

        wp_enqueue_style(
            'cddu-admin-organization',
            CDDU_MNGR_URL . 'assets/css/admin-organization.css',
            [],
            CDDU_MNGR_VERSION
        );

        wp_enqueue_style(
            'cddu-admin-metaboxes',
            CDDU_MNGR_URL . 'assets/css/admin-metaboxes.css',
            [],
            CDDU_MNGR_VERSION
        );

        wp_enqueue_script(
            'cddu-admin-organization',
            CDDU_MNGR_URL . 'assets/js/admin-organization.js',
            ['jquery', 'wp-i18n'],
            CDDU_MNGR_VERSION,
            true
        );
        
        wp_set_script_translations('cddu-admin-organization', 'wp-cddu-manager');
    }

    public function register(): void {
        // Register post types through their respective managers
        $this->contractManager->register_post_types();
        $this->missionManager->register_post_type();
        $this->learnerManager->register_post_type();
        $this->organizationManager->register_post_type();
        $this->notificationManager->register_post_type();
        $this->signatureManager->register_post_type();
        $this->holidayCalendarManager->register_post_type();

        // Setup metaboxes and hooks
        add_action('save_post_cddu_organization', [$this->organizationManager, 'save_organization_meta'], 10, 2);
        add_action('save_post_cddu_mission', [$this->missionManager, 'save_mission_meta'], 10, 2);
        add_action('save_post_cddu_learner', [$this->learnerManager, 'save_learner_meta'], 10, 2);
        add_action('save_post_cddu_notification', [$this->notificationManager, 'save_notification_meta'], 10, 2);
        add_action('save_post_cddu_signature', [$this->signatureManager, 'save_signature_meta'], 10, 2);
        add_action('save_post_cddu_holiday', [$this->holidayCalendarManager, 'save_holiday_calendar_meta'], 10, 2);
        
        // Remove pending status from learner post type
        add_action('admin_footer-post.php', [$this->learnerManager, 'remove_pending_status_from_learners']);
        add_action('admin_footer-post-new.php', [$this->learnerManager, 'remove_pending_status_from_learners']);
        add_filter('wp_insert_post_data', [$this->learnerManager, 'prevent_pending_status_for_learners'], 10, 2);
    }
}
