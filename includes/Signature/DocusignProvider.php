<?php
namespace CDDU_Manager\Signature;

class DocusignProvider implements SignatureProviderInterface {
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array {
        // Stub minimal: la vraie intégration DocuSign nécessite OAuth et l'API Envelopes.
        return ['error' => 'DocuSign provider stub – à compléter (OAuth, Envelope, Recipient, Tabs).'];
    }
}
