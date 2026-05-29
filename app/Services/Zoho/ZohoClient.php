<?php

namespace App\Services\Zoho;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;
use App\Exceptions\Zoho\ZohoRateLimitException;
use App\Models\ZohoToken;

class ZohoClient
{
    protected ZohoTokenService $tokenService;
    protected string $baseUrl;
    protected string $orgId;

    public function __construct(ZohoTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->baseUrl = config('services.zoho.base_url', 'https://www.zohoapis.com/invoice/v3');
        $this->orgId = config('services.zoho.organization_id', '');
    }

    public function request(string $method, string $endpoint, array $data = [], array $params = [], bool $isRetry = false)
    {
        $accessToken = $this->tokenService->getAccessToken();
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $queryParams = array_merge(['organization_id' => $this->orgId], $params);
        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        ];

        try {
            $http = Http::withHeaders($headers);
            $response = null;

            if (strtoupper($method) === 'GET') {
                $response = $http->get($url, $queryParams);
            } elseif (strtoupper($method) === 'DELETE') {
                $response = $http->delete($url . '?' . http_build_query($queryParams));
            } elseif (strtoupper($method) === 'POST') {
                $formData = !empty($data) ? ['JSONString' => json_encode((object)$data)] : [];
                $response = $http->asForm()->post($url . '?' . http_build_query($queryParams), $formData);
            } elseif (strtoupper($method) === 'PUT') {
                $formData = !empty($data) ? ['JSONString' => json_encode((object)$data)] : [];
                $response = $http->asForm()->put($url . '?' . http_build_query($queryParams), $formData);
            }

            if ($response && $response->status() === 401 && !$isRetry) {
                // Force token refresh
                $tokenRecord = ZohoToken::first();
                if ($tokenRecord) {
                    $this->tokenService->refreshAccessToken($tokenRecord);
                }
                return $this->request($method, $endpoint, $data, $params, true);
            }

            if ($response && $response->status() === 429) {
                Log::warning("Zoho API Rate Limit Exceeded (429) on {$endpoint}.");
                throw new ZohoRateLimitException("Zoho API Rate Limit Exceeded.");
            }

            if (!$response || !$response->successful()) {
                $status = $response ? $response->status() : 'Unknown';
                $body = $response ? $response->body() : 'No response';
                Log::error("Zoho API Error on {$method} {$endpoint}. Status: {$status} Body: {$body}");
                throw new ZohoApiException("Zoho API Error: " . ($response ? ($response->json('message') ?: $body) : 'No response'));
            }

            return $response->json();
        } catch (ZohoRateLimitException $e) {
            throw $e;
        } catch (ZohoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unhandled exception during Zoho API request: " . $e->getMessage());
            throw new ZohoApiException("Unhandled Zoho API Exception: " . $e->getMessage());
        }
    }

    public function get(string $endpoint, array $params = [])
    {
        return $this->request('GET', $endpoint, [], $params);
    }

    public function getRaw(string $endpoint, array $params = [], array $headers = [])
    {
        $accessToken = $this->tokenService->getAccessToken();
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $queryParams = array_merge(['organization_id' => $this->orgId], $params);
        $allHeaders = array_merge([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        ], $headers);

        try {
            $response = Http::withHeaders($allHeaders)->get($url, $queryParams);

            if ($response->status() === 401) {
                // Force token refresh
                $tokenRecord = ZohoToken::first();
                if ($tokenRecord) {
                    $this->tokenService->refreshAccessToken($tokenRecord);
                }
                // Try once more
                $accessToken = $this->tokenService->getAccessToken();
                $allHeaders['Authorization'] = 'Zoho-oauthtoken ' . $accessToken;
                $response = Http::withHeaders($allHeaders)->get($url, $queryParams);
            }

            if ($response->status() === 429) {
                throw new ZohoRateLimitException("Zoho API Rate Limit Exceeded.");
            }

            if (!$response->successful()) {
                throw new ZohoApiException("Zoho API Error: " . $response->body());
            }

            return $response->body();
        } catch (\Exception $e) {
            Log::error("Unhandled exception during Zoho API getRaw: " . $e->getMessage());
            throw new ZohoApiException("Unhandled Zoho API Exception: " . $e->getMessage());
        }
    }

    public function post(string $endpoint, array $data = [])
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = [])
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }
}

