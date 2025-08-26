<?php
namespace CDDU_Manager\Signature;

class YousignProvider implements SignatureProviderInterface {
    
    private string $api_base;
    private string $api_key;
    
    public function __construct() {
        $this->api_base = rtrim(get_option('cddu_yousign_base_url', 'https://api.yousign.com'), '/');
        $this->api_key = get_option('cddu_yousign_api_key', '');
    }
    
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array {
        if (!$this->api_key) {
            return ['error' => __('Missing Yousign API key', 'wp-cddu-manager')];
        }

        try {
            // 1) Upload file (stub: encode as base64)
            $fileB64 = base64_encode(file_get_contents($pdf_path));
            $headers = [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ];

            // Upload document
            $body = [
                'name' => $meta['name'] ?? basename($pdf_path),
                'content' => $fileB64,
            ];
            $resp = wp_remote_post($this->api_base . '/files', [
                'headers' => $headers, 
                'body' => wp_json_encode($body),
                'timeout' => 30
            ]);
            
            if (is_wp_error($resp)) {
                return ['error' => $resp->get_error_message()];
            }
            
            $file = json_decode(wp_remote_retrieve_body($resp), true);
            $fileId = $file['id'] ?? null;
            
            if (!$fileId) {
                return ['error' => __('Yousign file upload failed', 'wp-cddu-manager')];
            }

            // 2) Create the procedure / signature request with webhook
            $webhook_url = home_url('/wp-json/cddu-manager/v1/webhook/yousign');
            
            $procBody = [
                'name' => $meta['procedure_name'] ?? __('CDDU signature', 'wp-cddu-manager'),
                'description' => $meta['description'] ?? '',
                'webhook' => [
                    'url' => $webhook_url,
                    'method' => 'POST',
                    'headers' => [
                        'X-Custom-Contract-ID' => $meta['contract_id'] ?? '',
                        'X-Custom-Type' => $meta['document_type'] ?? 'contract'
                    ]
                ],
                'members' => [
                    [
                        'firstname' => $signer['firstname'] ?? '',
                        'lastname'  => $signer['lastname'] ?? '',
                        'email'     => $signer['email'] ?? '',
                        'phone'     => $signer['phone'] ?? null,
                    ]
                ],
                'files' => [
                    [
                        'id' => $fileId,
                        'members' => [
                            [
                                'id' => '@member_0', // Reference to first member
                                'position' => $meta['signature_position'] ?? [
                                    'page' => 1,
                                    'x' => 100,
                                    'y' => 100
                                ]
                            ]
                        ]
                    ]
                ],
                'advanced' => [
                    'auto_remind' => true,
                    'remind_interval' => 3, // days
                    'reminder_times' => 3
                ]
            ];
            
            $resp2 = wp_remote_post($this->api_base . '/procedures', [
                'headers' => $headers, 
                'body' => wp_json_encode($procBody),
                'timeout' => 30
            ]);
            
            if (is_wp_error($resp2)) {
                return ['error' => $resp2->get_error_message()];
            }
            
            $proc = json_decode(wp_remote_retrieve_body($resp2), true);
            $procedure_id = $proc['id'] ?? null;
            
            if (!$procedure_id) {
                return ['error' => __('Yousign procedure creation failed', 'wp-cddu-manager')];
            }
            
            // 3) Start the procedure
            $start_resp = wp_remote_post($this->api_base . '/procedures/' . $procedure_id . '/start', [
                'headers' => $headers,
                'timeout' => 30
            ]);
            
            if (is_wp_error($start_resp)) {
                return ['error' => $start_resp->get_error_message()];
            }
            
            $signature_url = $proc['members'][0]['signature_url'] ?? '';
            
            return [
                'request_id' => $procedure_id,
                'signature_url' => $signature_url,
                'status' => 'pending',
                'provider' => 'yousign'
            ];
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getSignatureStatus(string $request_id): array {
        if (!$this->api_key) {
            return ['error' => __('Missing Yousign API key', 'wp-cddu-manager')];
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type'  => 'application/json',
        ];
        
        $resp = wp_remote_get($this->api_base . '/procedures/' . $request_id, [
            'headers' => $headers,
            'timeout' => 30
        ]);
        
        if (is_wp_error($resp)) {
            return ['error' => $resp->get_error_message()];
        }
        
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        
        return [
            'status' => $this->mapStatus($data['status'] ?? 'unknown'),
            'signed_document_url' => $data['signed_document_url'] ?? null,
            'completed_at' => $data['finished_at'] ?? null,
            'raw_data' => $data
        ];
    }
    
    public function cancelSignatureRequest(string $request_id): array {
        if (!$this->api_key) {
            return ['error' => __('Missing Yousign API key', 'wp-cddu-manager')];
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type'  => 'application/json',
        ];
        
        $resp = wp_remote_request($this->api_base . '/procedures/' . $request_id, [
            'method' => 'DELETE',
            'headers' => $headers,
            'timeout' => 30
        ]);
        
        if (is_wp_error($resp)) {
            return ['error' => $resp->get_error_message()];
        }
        
        return ['success' => true];
    }
    
    public function handleWebhook(array $payload): array {
        // Validate webhook signature if configured
        $webhook_secret = get_option('cddu_yousign_webhook_secret', '');
        if ($webhook_secret) {
            $received_signature = $_SERVER['HTTP_X_YOUSIGN_SIGNATURE'] ?? '';
            $expected_signature = hash_hmac('sha256', wp_json_encode($payload), $webhook_secret);
            
            if (!hash_equals($expected_signature, $received_signature)) {
                return ['error' => 'Invalid webhook signature'];
            }
        }
        
        $procedure_id = $payload['procedure']['id'] ?? '';
        $status = $payload['procedure']['status'] ?? '';
        $event_type = $payload['event_type'] ?? '';
        
        // Map Yousign status to our internal status
        $mapped_status = $this->mapStatus($status);
        
        return [
            'procedure_id' => $procedure_id,
            'status' => $mapped_status,
            'event_type' => $event_type,
            'signed_document_url' => $payload['procedure']['signed_document_url'] ?? null,
            'completed_at' => $payload['procedure']['finished_at'] ?? null,
            'member_data' => $payload['procedure']['members'] ?? []
        ];
    }
    
    private function mapStatus(string $yousign_status): string {
        $status_map = [
            'draft' => 'pending',
            'active' => 'pending',
            'finished' => 'signed',
            'expired' => 'expired',
            'cancelled' => 'cancelled',
            'refused' => 'refused'
        ];
        
        return $status_map[$yousign_status] ?? 'unknown';
    }
}
