<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ZohoSetup extends Command
{
    protected $signature = 'zoho:setup {--code= : The grant token/authorization code from Zoho}';
    protected $description = 'Complete Zoho OAuth setup: exchange code for refresh token, fetch org ID, and update .env';

    public function handle()
    {
        $clientId = config('services.zoho.client_id');
        $clientSecret = config('services.zoho.client_secret');

        if (!$clientId || !$clientSecret) {
            $this->error('ZOHO_CLIENT_ID and ZOHO_CLIENT_SECRET must be set in your .env file.');
            return 1;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║              ZOHO INVOICE OAuth 2.0 SETUP                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Step 1: Get the authorization code
        $code = $this->option('code');

        if (!$code) {
            $authUrl = "https://accounts.zoho.com/oauth/v2/auth?"
                . "scope=ZohoInvoice.fullaccess.all"
                . "&client_id={$clientId}"
                . "&response_type=code"
                . "&redirect_uri=http://localhost"
                . "&access_type=offline";

            $this->warn('Step 1: Open this URL in your browser and click Accept:');
            $this->info('');
            $this->line($authUrl);
            $this->info('');
            $this->warn('After accepting, your browser will redirect to a URL like:');
            $this->line('http://localhost/?code=1000.xxxxx.xxxxx');
            $this->info('');

            // Try to open browser automatically
            if (PHP_OS_FAMILY === 'Linux') {
                @exec("xdg-open " . escapeshellarg($authUrl) . " > /dev/null 2>&1 &");
                $this->info('🌐 Browser opened automatically!');
            }

            $this->info('');
            $code = $this->ask('Paste the FULL redirect URL or just the code value here');
        }

        // Extract code from URL if full URL was pasted
        if (str_contains($code, 'code=')) {
            parse_str(parse_url($code, PHP_URL_QUERY), $params);
            $code = $params['code'] ?? $code;
        }

        $this->info('');
        $this->info('📡 Exchanging authorization code for tokens...');

        // Step 2: Exchange code for access + refresh token
        $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => 'http://localhost',
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful() || isset($response->json()['error'])) {
            $this->error('❌ Token exchange failed!');
            $this->error('Response: ' . $response->body());
            $this->info('');
            $this->warn('Common causes:');
            $this->line('  • The code expired (valid for only 60 seconds)');
            $this->line('  • The code was already used');
            $this->line('  • Client ID/Secret mismatch');
            $this->line('');
            $this->info('Run this command again to get a fresh code.');
            return 1;
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'] ?? null;
        $refreshToken = $tokenData['refresh_token'] ?? null;

        if (!$accessToken || !$refreshToken) {
            $this->error('❌ Response did not contain access_token or refresh_token');
            $this->error('Response: ' . json_encode($tokenData, JSON_PRETTY_PRINT));
            return 1;
        }

        $this->info('✅ Tokens received successfully!');
        $this->info("   Access Token:  " . substr($accessToken, 0, 20) . '...');
        $this->info("   Refresh Token: " . substr($refreshToken, 0, 20) . '...');

        // Step 3: Fetch Organization ID
        $this->info('');
        $this->info('📡 Fetching Organization ID...');

        $orgResponse = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        ])->get('https://www.zohoapis.com/invoice/v3/organizations');

        $organizationId = null;
        $orgName = 'Unknown';

        if ($orgResponse->successful()) {
            $orgData = $orgResponse->json();
            $organizations = $orgData['organizations'] ?? [];

            if (count($organizations) > 0) {
                // Use default org, or first one
                foreach ($organizations as $org) {
                    if ($org['is_default_org'] ?? false) {
                        $organizationId = $org['organization_id'];
                        $orgName = $org['name'] ?? 'Unknown';
                        break;
                    }
                }
                // Fallback to first org
                if (!$organizationId) {
                    $organizationId = $organizations[0]['organization_id'];
                    $orgName = $organizations[0]['name'] ?? 'Unknown';
                }

                $this->info("✅ Organization found: {$orgName} (ID: {$organizationId})");

                if (count($organizations) > 1) {
                    $this->warn('   Multiple organizations found:');
                    foreach ($organizations as $i => $org) {
                        $default = ($org['is_default_org'] ?? false) ? ' [DEFAULT]' : '';
                        $this->line("   [{$i}] {$org['name']} - ID: {$org['organization_id']}{$default}");
                    }
                }
            } else {
                $this->warn('⚠️  No organizations found in response.');
                $this->line('Response: ' . json_encode($orgData, JSON_PRETTY_PRINT));
            }
        } else {
            $this->warn('⚠️  Could not fetch organizations: ' . $orgResponse->body());
        }

        // Step 4: Update .env file
        $this->info('');
        $this->info('📝 Updating .env file...');

        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Update ZOHO_REFRESH_TOKEN
        $envContent = preg_replace(
            '/^ZOHO_REFRESH_TOKEN=.*$/m',
            'ZOHO_REFRESH_TOKEN=' . $refreshToken,
            $envContent
        );

        // Update ZOHO_ORGANIZATION_ID
        if ($organizationId) {
            $envContent = preg_replace(
                '/^ZOHO_ORGANIZATION_ID=.*$/m',
                'ZOHO_ORGANIZATION_ID=' . $organizationId,
                $envContent
            );
        }

        File::put($envPath, $envContent);

        $this->info('✅ .env file updated successfully!');

        // Clear config cache
        $this->call('config:clear');

        // Step 5: Test the API connection
        $this->info('');
        $this->info('🧪 Testing Zoho Invoice API connection...');

        // Test listing invoices
        $testResponse = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'X-com-zoho-invoice-organizationid' => $organizationId,
        ])->get('https://www.zohoapis.com/invoice/v3/invoices', [
            'per_page' => 1,
        ]);

        if ($testResponse->successful()) {
            $invoiceData = $testResponse->json();
            $totalInvoices = count($invoiceData['invoices'] ?? []);
            $this->info("✅ API connection successful! Found invoices in your account.");
        } else {
            $this->warn('⚠️  Invoice list test returned: ' . $testResponse->status());
            $this->line('Response: ' . $testResponse->body());
        }

        // Test listing contacts
        $contactResponse = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'X-com-zoho-invoice-organizationid' => $organizationId,
        ])->get('https://www.zohoapis.com/invoice/v3/contacts', [
            'per_page' => 1,
        ]);

        if ($contactResponse->successful()) {
            $this->info("✅ Contacts API accessible!");
        }

        // Summary
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    SETUP COMPLETE! ✅                        ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info("   Organization:     {$orgName}");
        $this->info("   Organization ID:  {$organizationId}");
        $this->info("   Refresh Token:    " . substr($refreshToken, 0, 20) . '...');
        $this->info("   Base URL:         https://www.zohoapis.com/invoice/v3");
        $this->info('');
        $this->info('Your .env has been updated. You can now use the Zoho Invoice API!');
        $this->info('');

        return 0;
    }
}
