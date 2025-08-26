<?php
namespace CDDU_Manager;

class DocumentArchive {
    
    public function __construct() {
        add_action('cddu_document_signatures_completed', [$this, 'archive_signed_document'], 10, 2);
        add_action('admin_menu', [$this, 'add_archive_menu']);
        add_action('wp_ajax_cddu_download_archive', [$this, 'ajax_download_archive']);
        
        // Add metaboxes for document relationships
        add_action('add_meta_boxes', [$this, 'add_relationship_metaboxes']);
    }

    public function add_archive_menu(): void {
        add_submenu_page(
            'edit.php?post_type=cddu_contract',
            __('Document Archive', 'wp-cddu-manager'),
            __('Archive', 'wp-cddu-manager'),
            'cddu_manage',
            'document-archive',
            [$this, 'render_archive_page']
        );
    }

    public function add_relationship_metaboxes(): void {
        add_meta_box(
            'cddu_contract_relationships',
            __('Related Documents', 'wp-cddu-manager'),
            [$this, 'render_contract_relationships_metabox'],
            'cddu_contract',
            'side',
            'default'
        );
        
        add_meta_box(
            'cddu_addendum_relationships',
            __('Parent Contract', 'wp-cddu-manager'),
            [$this, 'render_addendum_relationships_metabox'],
            'cddu_addendum',
            'side',
            'default'
        );
    }

    public function render_contract_relationships_metabox(\WP_Post $post): void {
        // Show related addendums
        $addendums = get_posts([
            'post_type' => 'cddu_addendum',
            'meta_query' => [
                [
                    'key' => 'parent_contract_id',
                    'value' => $post->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'ASC'
        ]);

        // Show signature requests
        $signature_requests = get_posts([
            'post_type' => 'cddu_signature_request',
            'meta_query' => [
                [
                    'key' => 'document_id',
                    'value' => $post->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        // Show timesheets
        $contract_data = get_post_meta($post->ID, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
        
        $timesheets = [];
        if ($instructor_user_id) {
            $timesheets = get_posts([
                'post_type' => 'cddu_timesheet',
                'meta_query' => [
                    [
                        'key' => 'contract_id',
                        'value' => $post->ID,
                        'compare' => '='
                    ]
                ],
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
        }

        ?>
        <div class="cddu-relationships">
            <?php if (!empty($addendums)): ?>
                <h4><?php echo esc_html__('Addendums', 'wp-cddu-manager'); ?> (<?php echo count($addendums); ?>)</h4>
                <ul>
                    <?php foreach ($addendums as $addendum): ?>
                        <li>
                            <a href="<?php echo admin_url('post.php?post=' . $addendum->ID . '&action=edit'); ?>">
                                <?php echo esc_html($addendum->post_title); ?>
                            </a>
                            <small>(<?php echo esc_html(get_the_date('d/m/Y', $addendum)); ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($signature_requests)): ?>
                <h4><?php echo esc_html__('Signature Requests', 'wp-cddu-manager'); ?> (<?php echo count($signature_requests); ?>)</h4>
                <ul>
                    <?php foreach ($signature_requests as $request): ?>
                        <?php
                        $status = get_post_meta($request->ID, 'status', true);
                        $signer_name = get_post_meta($request->ID, 'signer_name', true);
                        ?>
                        <li>
                            <strong><?php echo esc_html($signer_name); ?></strong>
                            <span class="status status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                            <small>(<?php echo esc_html(get_the_date('d/m/Y', $request)); ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($timesheets)): ?>
                <h4><?php echo esc_html__('Timesheets', 'wp-cddu-manager'); ?> (<?php echo count($timesheets); ?>)</h4>
                <ul>
                    <?php foreach ($timesheets as $timesheet): ?>
                        <?php
                        $month = get_post_meta($timesheet->ID, 'month', true);
                        $year = get_post_meta($timesheet->ID, 'year', true);
                        $hours = get_post_meta($timesheet->ID, 'hours_worked', true);
                        $status = get_post_meta($timesheet->ID, 'status', true);
                        ?>
                        <li>
                            <a href="<?php echo admin_url('post.php?post=' . $timesheet->ID . '&action=edit'); ?>">
                                <?php echo esc_html($month . ' ' . $year); ?>
                            </a>
                            - <?php echo esc_html($hours); ?>h
                            <span class="status status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="archive-actions">
                <h4><?php echo esc_html__('Archive Actions', 'wp-cddu-manager'); ?></h4>
                <p>
                    <a href="#" onclick="downloadContractArchive(<?php echo $post->ID; ?>)" class="button">
                        <?php echo esc_html__('Download Complete Archive', 'wp-cddu-manager'); ?>
                    </a>
                </p>
                <?php
                $pdf_url = get_post_meta($post->ID, 'generated_pdf_url', true);
                $signed_pdf_url = get_post_meta($post->ID, 'signed_document_url', true);
                ?>
                <?php if ($pdf_url): ?>
                    <p>
                        <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" class="button">
                            <?php echo esc_html__('View Original PDF', 'wp-cddu-manager'); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <?php if ($signed_pdf_url): ?>
                    <p>
                        <a href="<?php echo esc_url($signed_pdf_url); ?>" target="_blank" class="button button-primary">
                            <?php echo esc_html__('View Signed PDF', 'wp-cddu-manager'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <script>
        function downloadContractArchive(contractId) {
            window.location.href = ajaxurl + '?action=cddu_download_archive&contract_id=' + contractId + '&nonce=<?php echo wp_create_nonce('cddu_archive_nonce'); ?>';
        }
        </script>

        <style>
        .cddu-relationships ul {
            margin: 0;
            padding-left: 15px;
        }
        .cddu-relationships li {
            margin: 5px 0;
        }
        .cddu-relationships .status {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .archive-actions .button {
            display: block;
            margin: 5px 0;
            text-align: center;
        }
        </style>
        <?php
    }

    public function render_addendum_relationships_metabox(\WP_Post $post): void {
        $parent_contract_id = get_post_meta($post->ID, 'parent_contract_id', true);
        
        if ($parent_contract_id) {
            $parent_contract = get_post($parent_contract_id);
            if ($parent_contract) {
                ?>
                <p>
                    <strong><?php echo esc_html__('Parent Contract:', 'wp-cddu-manager'); ?></strong><br>
                    <a href="<?php echo admin_url('post.php?post=' . $parent_contract_id . '&action=edit'); ?>">
                        <?php echo esc_html($parent_contract->post_title); ?>
                    </a>
                </p>
                <?php
            }
        } else {
            ?>
            <p><?php echo esc_html__('No parent contract linked.', 'wp-cddu-manager'); ?></p>
            <?php
        }

        // Show signature requests for this addendum
        $signature_requests = get_posts([
            'post_type' => 'cddu_signature_request',
            'meta_query' => [
                [
                    'key' => 'document_id',
                    'value' => $post->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        if (!empty($signature_requests)): ?>
            <h4><?php echo esc_html__('Signature Requests', 'wp-cddu-manager'); ?></h4>
            <ul>
                <?php foreach ($signature_requests as $request): ?>
                    <?php
                    $status = get_post_meta($request->ID, 'status', true);
                    $signer_name = get_post_meta($request->ID, 'signer_name', true);
                    ?>
                    <li>
                        <strong><?php echo esc_html($signer_name); ?></strong>
                        <span class="status status-<?php echo esc_attr($status); ?>">
                            <?php echo esc_html(ucfirst($status)); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif;
    }

    public function render_archive_page(): void {
        // Get all contracts with their related documents
        $contracts = get_posts([
            'post_type' => 'cddu_contract',
            'numberposts' => -1,
            'post_status' => ['draft', 'publish'],
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Document Archive', 'wp-cddu-manager'); ?></h1>
            
            <div class="archive-stats">
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3><?php echo count($contracts); ?></h3>
                        <p><?php echo esc_html__('Total Contracts', 'wp-cddu-manager'); ?></p>
                    </div>
                    
                    <?php
                    $signed_contracts = array_filter($contracts, function($contract) {
                        return get_post_meta($contract->ID, 'signature_status', true) === 'completed';
                    });
                    ?>
                    <div class="stat-box">
                        <h3><?php echo count($signed_contracts); ?></h3>
                        <p><?php echo esc_html__('Signed Contracts', 'wp-cddu-manager'); ?></p>
                    </div>
                    
                    <?php
                    $total_addendums = get_posts([
                        'post_type' => 'cddu_addendum',
                        'numberposts' => -1,
                        'fields' => 'ids'
                    ]);
                    ?>
                    <div class="stat-box">
                        <h3><?php echo count($total_addendums); ?></h3>
                        <p><?php echo esc_html__('Total Addendums', 'wp-cddu-manager'); ?></p>
                    </div>
                    
                    <?php
                    $total_timesheets = get_posts([
                        'post_type' => 'cddu_timesheet',
                        'numberposts' => -1,
                        'fields' => 'ids'
                    ]);
                    ?>
                    <div class="stat-box">
                        <h3><?php echo count($total_timesheets); ?></h3>
                        <p><?php echo esc_html__('Total Timesheets', 'wp-cddu-manager'); ?></p>
                    </div>
                </div>
            </div>

            <div class="archive-contracts">
                <h2><?php echo esc_html__('Contract Archive', 'wp-cddu-manager'); ?></h2>
                
                <?php if (empty($contracts)): ?>
                    <p><?php echo esc_html__('No contracts found.', 'wp-cddu-manager'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Contract', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Instructor', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Period', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Status', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Addendums', 'wp-cddu-manager'); ?></th>
                                <th><?php echo esc_html__('Actions', 'wp-cddu-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): ?>
                                <?php
                                $contract_data = get_post_meta($contract->ID, 'contract_data', true);
                                $contract_data = maybe_unserialize($contract_data);
                                $signature_status = get_post_meta($contract->ID, 'signature_status', true) ?: 'draft';
                                
                                $instructor_user_id = $contract_data['instructor_user_id'] ?? 0;
                                $instructor_user = $instructor_user_id ? get_userdata($instructor_user_id) : null;
                                $instructor_name = $instructor_user ? $instructor_user->display_name : '-';
                                
                                $addendums = get_posts([
                                    'post_type' => 'cddu_addendum',
                                    'meta_query' => [
                                        [
                                            'key' => 'parent_contract_id',
                                            'value' => $contract->ID,
                                            'compare' => '='
                                        ]
                                    ],
                                    'numberposts' => -1,
                                    'fields' => 'ids'
                                ]);
                                ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="<?php echo admin_url('post.php?post=' . $contract->ID . '&action=edit'); ?>">
                                                <?php echo esc_html($contract->post_title); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small><?php echo esc_html(get_the_date('d/m/Y', $contract)); ?></small>
                                    </td>
                                    <td><?php echo esc_html($instructor_name); ?></td>
                                    <td>
                                        <?php if (!empty($contract_data['start_date']) && !empty($contract_data['end_date'])): ?>
                                            <?php
                                            $start = new DateTime($contract_data['start_date']);
                                            $end = new DateTime($contract_data['end_date']);
                                            echo esc_html($start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'));
                                            ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo esc_attr($signature_status); ?>">
                                            <?php echo esc_html(ucfirst($signature_status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo count($addendums); ?></td>
                                    <td>
                                        <a href="#" onclick="downloadContractArchive(<?php echo $contract->ID; ?>)" class="button button-small">
                                            <?php echo esc_html__('Download Archive', 'wp-cddu-manager'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <script>
        function downloadContractArchive(contractId) {
            window.location.href = ajaxurl + '?action=cddu_download_archive&contract_id=' + contractId + '&nonce=<?php echo wp_create_nonce('cddu_archive_nonce'); ?>';
        }
        </script>

        <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            margin: 0;
            font-size: 36px;
            color: #0073aa;
        }
        .stat-box p {
            margin: 10px 0 0 0;
            color: #666;
        }
        </style>
        <?php
    }

    public function ajax_download_archive(): void {
        check_ajax_referer('cddu_archive_nonce', 'nonce');
        
        if (!current_user_can('cddu_manage')) {
            wp_die(__('Access denied', 'wp-cddu-manager'));
        }
        
        $contract_id = intval($_GET['contract_id'] ?? 0);
        if (!$contract_id) {
            wp_die(__('Invalid contract ID', 'wp-cddu-manager'));
        }
        
        try {
            $archive_path = $this->create_contract_archive($contract_id);
            
            if (!$archive_path || !file_exists($archive_path)) {
                wp_die(__('Failed to create archive', 'wp-cddu-manager'));
            }
            
            // Send file for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($archive_path) . '"');
            header('Content-Length: ' . filesize($archive_path));
            readfile($archive_path);
            
            // Clean up temporary file
            unlink($archive_path);
            exit;
            
        } catch (\Exception $e) {
            wp_die(__('Error creating archive: ', 'wp-cddu-manager') . $e->getMessage());
        }
    }

    public function archive_signed_document(int $document_id, string $document_type): void {
        // Archive the completed document with all related files
        $archive_data = [
            'document_id' => $document_id,
            'document_type' => $document_type,
            'archived_date' => current_time('mysql'),
            'archive_reason' => 'signatures_completed'
        ];
        
        update_post_meta($document_id, 'archive_data', maybe_serialize($archive_data));
        update_post_meta($document_id, 'archived', true);
        
        // Create backup of all related documents
        $this->create_document_backup($document_id, $document_type);
    }

    private function create_contract_archive(int $contract_id): string {
        $contract = get_post($contract_id);
        if (!$contract) {
            throw new \Exception(__('Contract not found', 'wp-cddu-manager'));
        }

        $upload_dir = wp_upload_dir();
        $archive_filename = sprintf('contract-%d-archive-%s.zip', $contract_id, date('Y-m-d-H-i-s'));
        $archive_path = $upload_dir['path'] . '/' . $archive_filename;

        $zip = new \ZipArchive();
        if ($zip->open($archive_path, \ZipArchive::CREATE) !== TRUE) {
            throw new \Exception(__('Cannot create zip file', 'wp-cddu-manager'));
        }

        // Add contract PDF
        $contract_pdf = get_post_meta($contract_id, 'generated_pdf_path', true);
        if ($contract_pdf && file_exists($contract_pdf)) {
            $zip->addFile($contract_pdf, 'contract-original.pdf');
        }

        // Add signed contract PDF
        $signed_pdf = get_post_meta($contract_id, 'signed_document_path', true);
        if ($signed_pdf && file_exists($signed_pdf)) {
            $zip->addFile($signed_pdf, 'contract-signed.pdf');
        }

        // Add addendums
        $addendums = get_posts([
            'post_type' => 'cddu_addendum',
            'meta_query' => [
                [
                    'key' => 'parent_contract_id',
                    'value' => $contract_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        foreach ($addendums as $index => $addendum) {
            $addendum_pdf = get_post_meta($addendum->ID, 'generated_pdf_path', true);
            if ($addendum_pdf && file_exists($addendum_pdf)) {
                $zip->addFile($addendum_pdf, sprintf('addendum-%d-original.pdf', $index + 1));
            }

            $signed_addendum_pdf = get_post_meta($addendum->ID, 'signed_document_path', true);
            if ($signed_addendum_pdf && file_exists($signed_addendum_pdf)) {
                $zip->addFile($signed_addendum_pdf, sprintf('addendum-%d-signed.pdf', $index + 1));
            }
        }

        // Add summary document
        $summary = $this->generate_archive_summary($contract_id);
        $zip->addFromString('archive-summary.txt', $summary);

        $zip->close();

        return $archive_path;
    }

    private function create_document_backup(int $document_id, string $document_type): void {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/cddu-archive/' . date('Y/m');
        
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        // Copy all related files to backup directory
        $files_to_backup = [
            'generated_pdf_path',
            'signed_document_path'
        ];

        foreach ($files_to_backup as $meta_key) {
            $file_path = get_post_meta($document_id, $meta_key, true);
            if ($file_path && file_exists($file_path)) {
                $backup_filename = sprintf('%s-%d-%s-%s', 
                    $document_type, 
                    $document_id, 
                    $meta_key, 
                    basename($file_path)
                );
                $backup_path = $backup_dir . '/' . $backup_filename;
                copy($file_path, $backup_path);
                
                // Store backup path
                update_post_meta($document_id, 'backup_' . $meta_key, $backup_path);
            }
        }
    }

    private function generate_archive_summary(int $contract_id): string {
        $contract = get_post($contract_id);
        $contract_data = get_post_meta($contract_id, 'contract_data', true);
        $contract_data = maybe_unserialize($contract_data);
        
        $summary = sprintf(
            "CDDU CONTRACT ARCHIVE SUMMARY\n" .
            "=============================\n\n" .
            "Contract ID: %d\n" .
            "Contract Title: %s\n" .
            "Created Date: %s\n" .
            "Archive Date: %s\n\n",
            $contract_id,
            $contract->post_title,
            get_the_date('Y-m-d H:i:s', $contract),
            current_time('Y-m-d H:i:s')
        );

        if ($contract_data) {
            $summary .= sprintf(
                "MISSION DETAILS\n" .
                "===============\n" .
                "Action: %s\n" .
                "Location: %s\n" .
                "Period: %s to %s\n" .
                "Annual Hours: %s\n" .
                "Hourly Rate: %s €\n\n",
                $contract_data['action'] ?? '',
                $contract_data['location'] ?? '',
                $contract_data['start_date'] ?? '',
                $contract_data['end_date'] ?? '',
                $contract_data['annual_hours'] ?? '',
                $contract_data['hourly_rate'] ?? ''
            );
        }

        // Add addendums info
        $addendums = get_posts([
            'post_type' => 'cddu_addendum',
            'meta_query' => [
                [
                    'key' => 'parent_contract_id',
                    'value' => $contract_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        if (!empty($addendums)) {
            $summary .= "ADDENDUMS\n=========\n";
            foreach ($addendums as $addendum) {
                $summary .= sprintf(
                    "- %s (Created: %s)\n",
                    $addendum->post_title,
                    get_the_date('Y-m-d', $addendum)
                );
            }
            $summary .= "\n";
        }

        // Add signature info
        $signature_status = get_post_meta($contract_id, 'signature_status', true);
        $signatures_completed = get_post_meta($contract_id, 'signatures_completed_date', true);
        
        $summary .= sprintf(
            "SIGNATURE STATUS\n" .
            "================\n" .
            "Status: %s\n" .
            "Completed Date: %s\n\n",
            ucfirst($signature_status ?: 'Unknown'),
            $signatures_completed ?: 'Not completed'
        );

        $summary .= "FILES IN THIS ARCHIVE\n" .
                   "=====================\n" .
                   "- contract-original.pdf (Original contract)\n" .
                   "- contract-signed.pdf (Signed contract)\n" .
                   "- addendum-*-original.pdf (Original addendums)\n" .
                   "- addendum-*-signed.pdf (Signed addendums)\n" .
                   "- archive-summary.txt (This file)\n";

        return $summary;
    }
}
