<?php
namespace CDDU_Manager\Signature;

interface SignatureProviderInterface {
    /**
     * Crée une demande de signature à partir d'un PDF (chemin local) et d'un signataire.
     * Retourne un array avec 'request_id' et 'sign_url' (si applicable).
     */
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array;
}
