<?php
namespace CDDU_Manager;

use CDDU_Manager\Signature\SignatureProviderInterface;

class SignatureManager {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_webhook_endpoints']);
        add_action('cddu_send_for_signature', [$this, 'send_document_for_signature'], 10, 3);
    }

    public function register_webhook_endpoints(): void {
        register_rest_route('cddu-manager/v1', '/webhook/yousign', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_yousign_webhook'],
            'permission_callback' => '__return_true', // Public endpoint for webhooks
        ]);
        
        register_rest_route('cddu-manager/v1', '/webhook/docusign', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_docusign_webhook'],
            'permission_callback' => '__return_true', // Public endpoint for webhooks
        ]);
    }

    /**
     * Send a document for electronic signature
     */
    public function send_document_for_signature(int $document_id, string $document_type, array $signers): array {
        // Generate PDF if not already generated
        if ($document_type === 'contract') {
            $pdf_url = get_post_meta($document_id, 'generated_pdf_path', true);
            if (!$pdf_url || !file_exists($pdf_url)) {
                $pdf_url = DocumentGenerator::generateContractPdf($document_id);
            }
        } elseif ($document_type === 'addendum') {
            $pdf_url = get_post_meta($document_id, 'generated_pdf_path', true);
            if (!$pdf_url || !file_exists($pdf_url)) {
                $pdf_url = DocumentGenerator::generateAddendumPdf($document_id);
            }
        } else {
            return ['error' => __('Invalid document type', 'wp-cddu-manager')];
        }

        if (!$pdf_url || !file_exists($pdf_url)) {
            return ['error' => __('PDF file not found', 'wp-cddu-manager')];
        }

        $provider = Plugin::signature_provider();
        $results = [];

        foreach ($signers as $signer) {
            $meta = [
                'contract_id' => $document_id,
                'document_type' => $document_type,
                'name' => basename($pdf_url),
                'procedure_name' => sprintf(
                    __('%s - %s', 'wp-cddu-manager'),
                    ucfirst($document_type),
                    get_the_title($document_id)
                ),
                'description' => sprintf(
                    __('Please sign this %s document', 'wp-cddu-manager'),
                    $document_type
                )
            ];

            $result = $provider->createSignatureRequest($pdf_url, $signer, $meta);
            
            if (isset($result['error'])) {
                $results[] = ['signer' => $signer, 'error' => $result['error']];
                continue;
            }

            // Store signature request details
            $signature_request_id = wp_insert_post([
                'post_type' => 'cddu_signature_request',
                'post_title' => sprintf(
                    __('Signature Request - %s - %s', 'wp-cddu-manager'),
                    $signer['email'],
                    get_the_title($document_id)
                ),
                'post_status' => 'publish',
                'meta_input' => [
                    'document_id' => $document_id,
                    'document_type' => $document_type,
                    'signer_email' => $signer['email'],
                    'signer_name' => $signer['name'] ?? $signer['firstname'] . ' ' . $signer['lastname'],
                    'provider_request_id' => $result['request_id'],
                    'signature_url' => $result['signature_url'] ?? '',
                    'status' => 'pending',
                    'provider' => get_option('cddu_signature_provider', 'yousign'),
                    'created_date' => current_time('mysql'),
                    'provider_response' => maybe_serialize($result)
                ]
            ]);

            $results[] = [
                'signer' => $signer,
                'signature_request_id' => $signature_request_id,
                'provider_request_id' => $result['request_id'],
                'signature_url' => $result['signature_url'] ?? '',
                'status' => 'pending'
            ];
        }

        // Update document status
        update_post_meta($document_id, 'signature_status', 'pending');
        update_post_meta($document_id, 'signature_requests', maybe_serialize($results));
        update_post_meta($document_id, 'signature_sent_date', current_time('mysql'));

        return $results;
    }

    /**
     * Handle Yousign webhook
     */
    public function handle_yousign_webhook(\WP_REST_Request $request): \WP_REST_Response {
        $payload = $request->get_json_params();
        
        if (empty($payload)) {
            return new \WP_REST_Response(['error' => 'Empty payload'], 400);
        }

        try {
            $provider = Plugin::signature_provider();
            if (!method_exists($provider, 'handleWebhook')) {
                return new \WP_REST_Response(['error' => 'Provider does not support webhooks'], 400);
            }

            $webhook_data = $provider->handleWebhook($payload);
            
            if (isset($webhook_data['error'])) {
                return new \WP_REST_Response(['error' => $webhook_data['error']], 400);
            }

            $this->process_signature_update($webhook_data);
            
            return new \WP_REST_Response(['status' => 'processed'], 200);
            
        } catch (\Exception $e) {
            error_log('CDDU Yousign webhook error: ' . $e->getMessage());
            return new \WP_REST_Response(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle DocuSign webhook
     */
    public function handle_docusign_webhook(\WP_REST_Request $request): \WP_REST_Response {
        // Similar to Yousign but for DocuSign
        return new \WP_REST_Response(['status' => 'not_implemented'], 501);
    }

    /**
     * Process signature status update from webhook
     */
    private function process_signature_update(array $webhook_data): void {
        $provider_request_id = $webhook_data['procedure_id'] ?? '';
        $status = $webhook_data['status'] ?? '';
        
        if (!$provider_request_id) {
            return;
        }

        // Find signature request by provider ID
        $signature_requests = get_posts([
            'post_type' => 'cddu_signature_request',
            'meta_query' => [
                [
                    'key' => 'provider_request_id',
                    'value' => $provider_request_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => 1
        ]);

        if (empty($signature_requests)) {
            return;
        }

        $signature_request = $signature_requests[0];
        $document_id = get_post_meta($signature_request->ID, 'document_id', true);
        $document_type = get_post_meta($signature_request->ID, 'document_type', true);

        // Update signature request status
        update_post_meta($signature_request->ID, 'status', $status);
        update_post_meta($signature_request->ID, 'updated_date', current_time('mysql'));
        update_post_meta($signature_request->ID, 'webhook_data', maybe_serialize($webhook_data));

        if ($status === 'signed') {
            update_post_meta($signature_request->ID, 'signed_date', current_time('mysql'));
            
            // Store signed document URL if available
            if (!empty($webhook_data['signed_document_url'])) {
                update_post_meta($signature_request->ID, 'signed_document_url', $webhook_data['signed_document_url']);
                $this->download_signed_document($signature_request->ID, $webhook_data['signed_document_url']);
            }
        }

        // Check if all signatures are complete for this document
        $this->check_document_signature_completion($document_id, $document_type);
        
        // Send notifications
        $this->send_signature_status_notification($signature_request->ID, $status);
    }

    /**
     * Check if all required signatures are complete for a document
     */
    private function check_document_signature_completion(int $document_id, string $document_type): void {
        $all_requests = get_posts([
            'post_type' => 'cddu_signature_request',
            'meta_query' => [
                [
                    'key' => 'document_id',
                    'value' => $document_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);

        $total_requests = count($all_requests);
        $signed_requests = 0;
        $pending_requests = 0;
        $failed_requests = 0;

        foreach ($all_requests as $request) {
            $status = get_post_meta($request->ID, 'status', true);
            
            if ($status === 'signed') {
                $signed_requests++;
            } elseif (in_array($status, ['pending', 'sent'])) {
                $pending_requests++;
            } else {
                $failed_requests++;
            }
        }

        // Update document signature status
        if ($signed_requests === $total_requests) {
            update_post_meta($document_id, 'signature_status', 'completed');
            update_post_meta($document_id, 'signatures_completed_date', current_time('mysql'));
            
            // Update document post status to published if it was draft
            $document = get_post($document_id);
            if ($document && $document->post_status === 'draft') {
                wp_update_post([
                    'ID' => $document_id,
                    'post_status' => 'publish'
                ]);
            }
            
            // Trigger completion actions
            do_action('cddu_document_signatures_completed', $document_id, $document_type);
            
        } elseif ($failed_requests > 0 && $pending_requests === 0) {
            update_post_meta($document_id, 'signature_status', 'failed');
        } else {
            update_post_meta($document_id, 'signature_status', 'partial');
        }
    }

    /**
     * Download and store signed document
     */
    private function download_signed_document(int $signature_request_id, string $signed_document_url): void {
        $document_id = get_post_meta($signature_request_id, 'document_id', true);
        $document_type = get_post_meta($signature_request_id, 'document_type', true);
        
        $upload_dir = wp_upload_dir();
        $filename = sprintf(
            'signed-%s-%d-%s.pdf',
            $document_type,
            $document_id,
            date('Y-m-d-H-i-s')
        );
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        // Download the signed document
        $response = wp_remote_get($signed_document_url, ['timeout' => 60]);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $file_content = wp_remote_retrieve_body($response);
            
            if (file_put_contents($file_path, $file_content)) {
                $file_url = $upload_dir['url'] . '/' . $filename;
                
                // Store signed document info
                update_post_meta($signature_request_id, 'signed_document_path', $file_path);
                update_post_meta($signature_request_id, 'signed_document_local_url', $file_url);
                update_post_meta($document_id, 'signed_document_path', $file_path);
                update_post_meta($document_id, 'signed_document_url', $file_url);
            }
        }
    }

    /**
     * Send notification about signature status change
     */
    private function send_signature_status_notification(int $signature_request_id, string $status): void {
        $document_id = get_post_meta($signature_request_id, 'document_id', true);
        $document_type = get_post_meta($signature_request_id, 'document_type', true);
        $signer_email = get_post_meta($signature_request_id, 'signer_email', true);
        $signer_name = get_post_meta($signature_request_id, 'signer_name', true);

        // Get organization managers to notify
        if ($document_type === 'contract') {
            $contract_data = get_post_meta($document_id, 'contract_data', true);
            $contract_data = maybe_unserialize($contract_data);
            $organization_id = $contract_data['organization_id'] ?? 0;
        } else {
            $parent_contract_id = get_post_meta($document_id, 'parent_contract_id', true);
            $contract_data = get_post_meta($parent_contract_id, 'contract_data', true);
            $contract_data = maybe_unserialize($contract_data);
            $organization_id = $contract_data['organization_id'] ?? 0;
        }

        if (!$organization_id) {
            return;
        }

        $managers = get_post_meta($organization_id, 'organization_managers', true);
        $managers = maybe_unserialize($managers) ?: [];

        if (empty($managers)) {
            return;
        }

        $status_messages = [
            'signed' => __('Document has been signed', 'wp-cddu-manager'),
            'refused' => __('Document signing was refused', 'wp-cddu-manager'),
            'expired' => __('Document signing has expired', 'wp-cddu-manager'),
            'cancelled' => __('Document signing was cancelled', 'wp-cddu-manager')
        ];

        $status_message = $status_messages[$status] ?? sprintf(__('Document status changed to: %s', 'wp-cddu-manager'), $status);

        $subject = sprintf(
            __('[%s] %s - %s', 'wp-cddu-manager'),
            get_bloginfo('name'),
            $status_message,
            get_the_title($document_id)
        );

        $message = sprintf(
            __('Document: %s%sType: %s%sSigner: %s (%s)%sStatus: %s%s', 'wp-cddu-manager'),
            get_the_title($document_id),
            "\n",
            ucfirst($document_type),
            "\n",
            $signer_name,
            $signer_email,
            "\n",
            $status_message,
            "\n\n"
        );

        $message .= sprintf(
            __('View details in admin: %s', 'wp-cddu-manager'),
            admin_url('post.php?post=' . $document_id . '&action=edit')
        );

        foreach ($managers as $manager_id) {
            $user = get_user_by('ID', $manager_id);
            if ($user) {
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
}
