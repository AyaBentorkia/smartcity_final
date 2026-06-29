<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $credentials         = json_decode(file_get_contents(storage_path('app/firebase-credentials.json')), true);
        $this->projectId     = $credentials['project_id'];
        $this->credentialsPath = storage_path('app/firebase-credentials.json');
    }

    private function getAccessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            json_decode(file_get_contents($this->credentialsPath), true)
        );

        $token = $credentials->fetchAuthToken();
        return $token['access_token'];
    }

    public function send(string $fcmToken, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'message' => [
                    'token'        => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_map('strval', $data), // FCM exige des strings
                ],
            ]);

            if ($response->failed()) {
                Log::error('FCM send failed', ['response' => $response->json()]);
            } else {
                Log::info('FCM sent successfully', ['fcm_token' => $fcmToken]);
            }

        } catch (\Exception $e) {
            Log::error('FCM exception', ['error' => $e->getMessage()]);
        }
    }
}