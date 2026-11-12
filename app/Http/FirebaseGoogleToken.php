<?php

namespace App\Http;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FirebaseGoogleToken
{
    private const CERTIFICATES = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /** @return array{subject: string, email: string, name: mixed}|null */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerPart, $payloadPart, $signaturePart] = $parts;
        $header = json_decode($this->decode($headerPart), true);
        $claims = json_decode($this->decode($payloadPart), true);
        $signature = $this->decode($signaturePart);

        if (! is_array($header) || ! is_array($claims) || ($header['alg'] ?? null) !== 'RS256'
            || ! is_string($keyId = $header['kid'] ?? null) || $signature === '') {
            return null;
        }

        $project = config('services.firebase.projectId');
        $now = now()->timestamp;
        $subject = $claims['firebase']['identities']['google.com'][0] ?? null;
        $email = $claims['email'] ?? null;

        if (! is_string($project) || $project === '' || ($claims['aud'] ?? null) !== $project
            || ($claims['iss'] ?? null) !== 'https://securetoken.google.com/'.$project
            || ! is_string($uid = $claims['sub'] ?? null) || $uid === '' || strlen($uid) > 128
            || ! is_int($expiry = $claims['exp'] ?? null) || $expiry <= $now
            || ! is_int($issuedAt = $claims['iat'] ?? null) || $issuedAt > $now
            || ! is_int($authTime = $claims['auth_time'] ?? null) || $authTime > $now || $authTime < $now - 300
            || ($claims['firebase']['sign_in_provider'] ?? null) !== 'google.com'
            || ($claims['email_verified'] ?? null) !== true
            || ! is_string($subject) || $subject === '' || strlen($subject) > 255
            || ! is_string($email) || strlen($email) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $certificate = $this->certificates()[$keyId] ?? null;
        if (! is_string($certificate) || openssl_verify($headerPart.'.'.$payloadPart, $signature, $certificate, OPENSSL_ALGO_SHA256) !== 1) {
            return null;
        }

        return ['subject' => $subject, 'email' => $email, 'name' => $claims['name'] ?? null];
    }

    private function decode(string $part): string
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/D', $part)) {
            return '';
        }

        return base64_decode(strtr($part, '-_', '+/'), true) ?: '';
    }

    private function certificates(): array
    {
        $cacheKey = 'firebase-secure-token-certificates';
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $response = Http::connectTimeout(3)->timeout(8)->get(self::CERTIFICATES);
        $certificates = $response->json();
        if (! $response->successful() || ! is_array($certificates)) {
            return [];
        }

        if (preg_match('/\bmax-age=(\d+)/i', $response->header('Cache-Control'), $matches) && (int) $matches[1] > 0) {
            Cache::put($cacheKey, $certificates, (int) $matches[1]);
        }

        return $certificates;
    }
}
