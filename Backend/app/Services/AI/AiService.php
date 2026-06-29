<?php
// app/Services/AI/AiService.php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AiService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.base_url');
        $this->timeout = config('services.ai.timeout', 30);
    }

    public function predictIncident(array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/predict", $payload);

            if ($response->failed()) {
                Log::error('AI Service error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new Exception("AI Service returned error: {$response->status()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('AI Service unavailable', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}