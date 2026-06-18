<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = app(App\Services\Zoho\ZohoClient::class);
$response = $client->post('/settings/taxes', [
    'tax_name' => 'VAT + Service Charge',
    'tax_type' => 'tax_group',
    'taxes' => [
        '8856924000000093308',
        '8856924000000347014'
    ]
]);
print_r($response);
