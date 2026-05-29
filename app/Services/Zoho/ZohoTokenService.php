<?php

namespace App\Services\Zoho;

use App\Models\ZohoToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;

class ZohoTokenService
{
    public function getAccessToken(): string
    {
        $tokenRecord = ZohoToken::first();

        if (!$tokenRecord) {
            $tokenRecord = ZohoToken::create([
                'access_token' => 'initial_dummy_token',
                'refresh_token' => config('services.zoho.refresh_token') ?? 'dummy_refresh_token',
                'expires_at' => now()->subMinutes(10),
            ]);
        }

        if ($tokenRecord->expires_at->isPast()) {
            return $this->refreshAccessToken($tokenRecord);
        }

        return $tokenRecord->access_token;
    }

    public function refreshAccessToken(ZohoToken $tokenRecord): string
    {
        $clientId = config('services.zoho.client_id');
        $clientSecret = config('services.zoho.client_secret');
        $refreshToken = $tokenRecord->refresh_token ?: config('services.zoho.refresh_token');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            Log::error('Zoho OAuth configuration missing', [
                'has_client_id' => !empty($clientId),
                'has_client_secret' => !empty($clientSecret),
                'has_refresh_token' => !empty($refreshToken),
            ]);
            throw new ZohoApiException('Zoho OAuth configuration is missing.');
        }

        Log::info('Zoho OAuth: Attempting token refresh', [
            'client_id_prefix' => substr($clientId, 0, 15) . '...',
            'refresh_token_prefix' => substr($refreshToken, 0, 15) . '...',
        ]);

        $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
        ]);

        $data = $response->json();

        // Zoho returns HTTP 200 even for errors like invalid_client
        if (!$response->successful() || isset($data['error'])) {
            $errorMsg = $data['error'] ?? ('HTTP ' . $response->status());
            Log::error('Zoho OAuth Token refresh failed', [
                'status' => $response->status(),
                'error' => $errorMsg,
                'body' => $response->body(),
            ]);
            throw new ZohoApiException('Failed to refresh Zoho OAuth Token: ' . $errorMsg . ' - ' . $response->body());
        }

        if (!isset($data['access_token'])) {
            Log::error('Zoho OAuth Token refresh did not return access_token. Body: ' . $response->body());
            throw new ZohoApiException('Invalid OAuth response: access_token missing. Body: ' . $response->body());
        }

        $tokenRecord->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return $data['access_token'];
    }
}
