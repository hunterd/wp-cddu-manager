<?php
namespace CDDU_Manager;

class PostTypes {
    public function register(): void {
        register_post_type('cddu_contract', [
            'label' => 'Contrats',
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-media-text',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        register_post_type('cddu_addendum', [
            'label' => 'Avenants',
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-media-document',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        register_post_type('cddu_timesheet', [
            'label' => 'Déclarations d\'heures',
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-clock',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);
    }
}
