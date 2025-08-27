<?php
namespace CDDU_Manager;

use CDDU_Manager\Admin\ContractManager;
use CDDU_Manager\Admin\MissionManager;
use CDDU_Manager\Admin\LearnerManager;
use CDDU_Manager\Admin\OrganizationManager;

class PostTypes {
    private $contractManager;
    private $missionManager;
    private $learnerManager;
    private $organizationManager;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // Initialize managers
        $this->contractManager = new ContractManager();
        $this->missionManager = new MissionManager();
        $this->learnerManager = new LearnerManager();
        $this->organizationManager = new OrganizationManager();
    }

    public function enqueue_admin_styles($hook): void {
        // Only load on organization edit pages
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        global $post_type;
        if (!in_array($post_type, ['cddu_organization', 'cddu_contract', 'cddu_mission', 'cddu_learner'])) {
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
            ['jquery'],
            CDDU_MNGR_VERSION,
            true
        );
    }

    public function register(): void {
        // Register post types through their respective managers
        $this->contractManager->register_post_types();
        $this->missionManager->register_post_type();
        $this->learnerManager->register_post_type();
        $this->organizationManager->register_post_type();

        // Register remaining post types that don't have dedicated managers yet
        register_post_type('cddu_notification', [
            'label' => __('Notifications', 'wp-cddu-manager'),
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-warning',
            'supports' => ['title', 'editor', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        register_post_type('cddu_signature', [
            'label' => __('Signature Requests', 'wp-cddu-manager'),
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-edit-page',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        // Setup metaboxes and hooks
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
        add_action('save_post_cddu_organization', [$this->organizationManager, 'save_organization_meta'], 10, 2);
        add_action('save_post_cddu_mission', [$this->missionManager, 'save_mission_meta'], 10, 2);
        add_action('save_post_cddu_learner', [$this->learnerManager, 'save_learner_meta'], 10, 2);
        
        // Remove pending status from learner post type
        add_action('admin_footer-post.php', [$this->learnerManager, 'remove_pending_status_from_learners']);
        add_action('admin_footer-post-new.php', [$this->learnerManager, 'remove_pending_status_from_learners']);
        add_filter('wp_insert_post_data', [$this->learnerManager, 'prevent_pending_status_for_learners'], 10, 2);
    }

    public function add_metaboxes(): void {
        $this->organizationManager->add_metaboxes();
        $this->missionManager->add_metaboxes();
        $this->learnerManager->add_metaboxes();
    }
}
