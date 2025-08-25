<?php
namespace CDDU_Manager\Signature;

class YousignProvider implements SignatureProviderInterface {
    public function createSignatureRequest(string $pdf_path, array $signer, array $meta = []): array {
        $api = rtrim(get_option('cddu_yousign_base_url','https://api.yousign.com'), '/');
        $key = get_option('cddu_yousign_api_key','');
        if (!$key) { return ['error' => 'Yousign API key manquante']; }

        // 1) Uploader le fichier (stub: on encode en base64)
        $fileB64 = base64_encode(file_get_contents($pdf_path));
        $headers = [
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ];

        // NB: l'API exacte peut différer, ceci est un stub d'implémentation.
        $body = [
            'name' => $meta['name'] ?? basename($pdf_path),
            'content' => $fileB64,
        ];
        $resp = wp_remote_post($api . '/files', ['headers' => $headers, 'body' => wp_json_encode($body)]);
        if (is_wp_error($resp)) { return ['error' => $resp->get_error_message()]; }
        $file = json_decode(wp_remote_retrieve_body($resp), true);
        $fileId = $file['id'] ?? null;
        if (!$fileId) { return ['error' => 'Upload fichier Yousign échoué']; }

        // 2) Créer la procédure / demande de signature
        $procBody = [
            'name' => $meta['procedure_name'] ?? 'Signature CDDU',
            'members' => [[
                'firstname' => $signer['firstname'] ?? '',
                'lastname'  => $signer['lastname'] ?? '',
                'email'     => $signer['email'] ?? '',
            ]],
            'files' => [[ 'id' => $fileId ]],
        ];
        $resp2 = wp_remote_post($api . '/procedures', ['headers' => $headers, 'body' => wp_json_encode($procBody)]);
        if (is_wp_error($resp2)) { return ['error' => $resp2->get_error_message()]; }
        $proc = json_decode(wp_remote_retrieve_body($resp2), true);
        return ['request_id' => $proc['id'] ?? null];
    }
}
