<?php

class JWTHandler {
    private string $secret;

    public function __construct(string $secret) {
        if (empty($secret)) {
            throw new Exception("Clé secrète JWT manquante.");
        }
        $this->secret = $secret;
    }

    // Génère un JWT à partir d'un tableau de données
    public function generate(array $payload): string {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    // Vérifie un JWT et retourne le payload si valide
    public function verify(string $jwt): ?array {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        [$header64, $payload64, $signature64] = $parts;
        $data = "$header64.$payload64";

        $expectedSignature = rtrim(strtr(base64_encode(
            hash_hmac('sha256', $data, $this->secret, true)
        ), '+/', '-_'), '=');

        if (!hash_equals($expectedSignature, $signature64)) return null;

        $payload = json_decode(base64_decode($payload64), true);
        if (!$payload || (isset($payload['exp']) && time() > $payload['exp'])) {
            return null;
        }

        return $payload;
    }
}
