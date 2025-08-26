<?php
namespace CDDU_Manager\Signature;

class DocusignProvider implements SignatureProviderInterface {
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array {
        // Minimal stub: real DocuSign integration requires OAuth and the Envelopes API.
        // Make the message translatable using the plugin text domain.
        return [
            'error' => __('DocuSign provider stub — to be completed (OAuth, Envelope, Recipient, Tabs).', 'wp-cddu-manager'),
        ];
    }
}
