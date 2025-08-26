<?php
namespace CDDU_Manager\Signature;

interface SignatureProviderInterface {
    // Common keys (use these English keys in signer/meta arrays)
    public const SIGNER_NAME = 'name';
    public const SIGNER_EMAIL = 'email';
    public const SIGNER_PHONE = 'phone';

    // Keys returned in the result array
    public const META_REQUEST_ID = 'request_id';
    public const META_SIGN_URL = 'sign_url';

    /**
     * Create a signature request from a local PDF path and a signer.
     *
     * The $signer array SHOULD use the English keys defined above (SIGNER_NAME, SIGNER_EMAIL, SIGNER_PHONE).
     * Any human-visible labels must be translated using WordPress i18n functions, for example:
     *   __('Sign here', 'wp-cddu-manager')
     *
     * Implementations must return an array containing at least:
     *   - META_REQUEST_ID => (string) provider request id
     *   - META_SIGN_URL  => (string|null) redirect URL for signing, if available
     *
     * @param string $pdf_path Local filesystem path to the PDF file
     * @param array $signer Associative array describing the signer (use the interface constants for keys)
     * @param array $meta Optional metadata passed to the provider
     * @return array Associative array with at least META_REQUEST_ID and optionally META_SIGN_URL
     */
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array;
    
    /**
     * Get the current status of a signature request
     *
     * @param string $request_id The provider's request ID
     * @return array Status information including 'status', 'signed_document_url', etc.
     */
    public function getSignatureStatus(string $request_id): array;
    
    /**
     * Cancel a pending signature request
     *
     * @param string $request_id The provider's request ID
     * @return array Result of cancellation
     */
    public function cancelSignatureRequest(string $request_id): array;
    
    /**
     * Handle webhook payload from signature provider
     *
     * @param array $payload The webhook payload
     * @return array Processed webhook data
     */
    public function handleWebhook(array $payload): array;
}
