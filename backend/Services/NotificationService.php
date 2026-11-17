<?php

declare(strict_types=1);

namespace Services;

use Models\DeviceToken;

class NotificationService
{
    /**
     * Send remote push notification via Firebase Cloud Messaging (FR-29).
     * If FCM service account or project is not configured, logs gracefully without failing message delivery.
     */
    public static function notifyNewMessage(string $recipientUserId, string $senderName, string $messageSnippet): void
    {
        $tokens = DeviceToken::getTokensForUser($recipientUserId);
        if (empty($tokens)) {
            return;
        }

        $serviceAccountFile = dirname(__DIR__) . '/firebase-service-account.json';
        if (!is_file($serviceAccountFile)) {
            // Log local simulated notification for evaluation
            error_log(sprintf('[FCM Simulate] Sending notification to %d device(s) for user %s: "%s: %s"', count($tokens), $recipientUserId, $senderName, $messageSnippet));
            return;
        }

        try {
            $serviceAccount = json_decode((string) file_get_contents($serviceAccountFile), true);
            $projectId = $serviceAccount['project_id'] ?? null;
            if (!$projectId) {
                return;
            }

            $accessToken = self::getGoogleAccessToken($serviceAccount);
            if (!$accessToken) {
                return;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => 'Pesan baru dari ' . $senderName,
                            'body' => mb_substr($messageSnippet, 0, 100),
                        ],
                        'data' => [
                            'sender_name' => $senderName,
                            'click_action' => 'OPEN_CHAT',
                        ],
                    ],
                ];

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $accessToken,
                        'Content-Type: application/json; UTF-8',
                    ],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 5,
                ]);
                $response = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // If token is invalid or unregistered (404), clean up
                if ($status === 404 || $status === 400) {
                    DeviceToken::deleteToken($recipientUserId, $token);
                }
            }
        } catch (\Throwable $e) {
            error_log('FCM Notification error: ' . $e->getMessage());
        }
    }

    /**
     * Obtains OAuth2 access token for Firebase HTTP v1 using Service Account JWT.
     */
    private static function getGoogleAccessToken(array $sa): ?string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $encode = static fn(array $data): string => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
        $rawToken = $encode($header) . '.' . $encode($claims);

        $privateKey = openssl_pkey_get_private($sa['private_key']);
        if (!$privateKey) {
            return null;
        }

        openssl_sign($rawToken, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $jwt = $rawToken . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            CURLOPT_TIMEOUT => 5,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode((string) $res, true);
        return $data['access_token'] ?? null;
    }
}
