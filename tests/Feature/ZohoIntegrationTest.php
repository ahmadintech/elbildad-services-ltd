<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Rfq;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ZohoToken;
use App\Enums\EstimateStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentModeEnum;
use App\Enums\QuantityEnum;
use App\Enums\DeliveryMethodEnum;
use App\Services\Zoho\ZohoTokenService;
use App\Services\Zoho\ZohoClient;
use App\Services\Zoho\ZohoContactService;
use App\Services\Zoho\ZohoItemService;
use App\Services\Zoho\ZohoEstimateService;
use App\Services\Zoho\ZohoInvoiceService;
use App\Services\Zoho\ZohoPaymentService;
use App\Exceptions\Zoho\ZohoApiException;
use App\Exceptions\Zoho\ZohoRateLimitException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ZohoIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.zoho.client_id' => 'test_client_id',
            'services.zoho.client_secret' => 'test_client_secret',
            'services.zoho.refresh_token' => 'test_refresh_token',
            'services.zoho.organization_id' => 'test_org_id',
            'services.zoho.base_url' => 'https://www.zohoapis.com/invoice/v3',
        ]);

        // Clean up database tokens between tests to guarantee mock purity
        ZohoToken::truncate();
    }

    public function test_token_service_retrieves_and_caches_access_token(): void
    {
        Http::fake([
            'https://accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'new_access_token_abc',
                'expires_in' => 3600,
            ], 200)
        ]);

        $service = new ZohoTokenService();
        $token = $service->getAccessToken();

        $this->assertEquals('new_access_token_abc', $token);
        
        $tokenRecord = ZohoToken::first();
        $this->assertNotNull($tokenRecord);
        $this->assertEquals('new_access_token_abc', $tokenRecord->access_token);
        $this->assertTrue($tokenRecord->expires_at->isFuture());
    }

    public function test_zoho_client_injects_headers_and_organization_query(): void
    {
        ZohoToken::create([
            'access_token' => 'existing_valid_token',
            'refresh_token' => 'refresh_token_123',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/contacts?organization_id=test_org_id' => Http::response(['message' => 'success'], 200)
        ]);

        $client = app(ZohoClient::class);
        $response = $client->get('/contacts');

        $this->assertEquals('success', $response['message']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Zoho-oauthtoken existing_valid_token')
                && str_contains($request->url(), 'organization_id=test_org_id');
        });
    }

    public function test_zoho_client_transforms_post_payload_into_jsonstring_form_data(): void
    {
        ZohoToken::create([
            'access_token' => 'existing_valid_token',
            'refresh_token' => 'refresh_token_123',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            '*' => Http::response(['message' => 'success'], 200)
        ]);

        $client = app(ZohoClient::class);
        $client->post('/contacts', ['name' => 'Test Account']);

        Http::assertSent(function ($request) {
            $isFormData = str_contains($request->header('Content-Type')[0], 'application/x-www-form-urlencoded');
            $data = [];
            parse_str($request->body(), $data);
            
            return $isFormData 
                && isset($data['JSONString']) 
                && str_contains($data['JSONString'], 'Test Account');
        });
    }

    public function test_zoho_client_handles_401_by_refreshing_token_and_retrying(): void
    {
        $tokenRecord = ZohoToken::create([
            'access_token' => 'expired_token',
            'refresh_token' => 'refresh_token_123',
            'expires_at' => now()->addHour(), // Simulate valid locally but expired remotely
        ]);

        Http::fake([
            'https://accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'brand_new_refreshed_token',
                'expires_in' => 3600,
            ], 200),
            'https://www.zohoapis.com/invoice/v3/contacts?organization_id=test_org_id' => Http::sequence()
                ->push(['message' => 'unauthorized'], 401)
                ->push(['message' => 'retry_success'], 200)
        ]);

        $client = app(ZohoClient::class);
        $response = $client->get('/contacts');

        $this->assertEquals('retry_success', $response['message']);
        
        $tokenRecord->refresh();
        $this->assertEquals('brand_new_refreshed_token', $tokenRecord->access_token);
    }

    public function test_zoho_client_handles_429_by_throwing_rate_limit_exception(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token_123',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            '*' => Http::response('Too Many Requests', 429)
        ]);

        $client = app(ZohoClient::class);

        $this->expectException(ZohoRateLimitException::class);
        $client->get('/contacts');
    }

    public function test_user_observer_synchronizes_customer_to_zoho_on_creation(): void
    {
        Http::fake([
            'https://www.zohoapis.com/invoice/v3/contacts?organization_id=test_org_id' => Http::response([
                'contact' => [
                    'contact_id' => 'zoho_customer_id_999'
                ]
            ], 201)
        ]);

        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $user = User::create([
            'name' => 'Jane Sourcing Expert',
            'email' => 'jane_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
            'whatsapp_number' => '+234' . rand(7000000000, 9999999999),
        ]);

        $user->refresh();
        $this->assertEquals('zoho_customer_id_999', $user->zoho_contact_id);
    }

    public function test_item_observer_synchronizes_to_zoho_on_creation(): void
    {
        Http::fake([
            'https://www.zohoapis.com/invoice/v3/items?organization_id=test_org_id' => Http::response([
                'item' => [
                    'item_id' => 'zoho_item_id_777'
                ]
            ], 201)
        ]);

        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $item = Item::create([
            'name' => 'Industrial Generator',
            'description' => '150kVA diesel power plant',
            'rate' => 25000.00,
            'unit' => 'pcs',
        ]);

        $item->refresh();
        $this->assertEquals('zoho_item_id_777', $item->zoho_item_id);
    }

    public function test_zoho_estimate_service_creates_estimate_correctly(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $customer = User::withoutEvents(function () {
            return User::create([
                'name' => 'Alice Client',
                'email' => 'alice_' . Str::random(5) . '@example.com',
                'password' => bcrypt('password'),
                'zoho_contact_id' => 'zoho_contact_111',
            ]);
        });

        $category = \App\Models\Category::create([
            'name' => 'Machinery',
            'slug' => 'machinery-' . Str::random(5),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'product_name' => 'Heavy Excavator',
            'specifications' => 'Yellow colored hydraulic track excavator',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $estId = 'zoho_estimate_id_222_' . Str::random(5);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/estimates?organization_id=test_org_id' => Http::response([
                'estimate' => [
                    'estimate_id' => $estId
                ]
            ], 201)
        ]);

        $estimateService = app(ZohoEstimateService::class);
        $result = $estimateService->createEstimate($rfq, [
            [
                'name' => 'Heavy Excavator Model X',
                'rate' => 85000.00,
                'quantity' => 1,
                'description' => 'Hydraulic tracked machinery',
            ]
        ]);

        $this->assertEquals($estId, $result['estimate_id']);
        
        $localEstimate = Estimate::where('zoho_estimate_id', $estId)->first();
        $this->assertNotNull($localEstimate);
        $this->assertEquals($customer->id, $localEstimate->customer_id);
        $this->assertEquals(EstimateStatusEnum::DRAFT, $localEstimate->status);
    }

    public function test_zoho_invoice_service_records_payment_and_balances_local_ledger(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $customer = User::withoutEvents(function () {
            return User::create([
                'name' => 'Bob Finance',
                'email' => 'bob_' . Str::random(5) . '@example.com',
                'password' => bcrypt('password'),
                'zoho_contact_id' => 'zoho_contact_222',
            ]);
        });

        $category = \App\Models\Category::create([
            'name' => 'Metals',
            'slug' => 'metals-' . Str::random(5),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'product_name' => 'Steel Pipes',
            'specifications' => '3 inch seamless',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $invId = 'zoho_invoice_id_888_' . Str::random(5);
        $invNum = 'INV-' . rand(10000, 99999);
        $payId = 'zoho_payment_id_999_' . Str::random(5);

        $invoice = Invoice::create([
            'rfq_id' => $rfq->id,
            'customer_id' => $customer->id,
            'zoho_invoice_id' => $invId,
            'invoice_number' => $invNum,
            'status' => InvoiceStatusEnum::SENT,
            'total' => 1000.00,
            'balance_due' => 1000.00,
            'currency' => 'USD',
            'due_date' => now()->addDays(10),
            'issued_at' => now(),
        ]);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/customerpayments?organization_id=test_org_id' => Http::response([
                'payment' => [
                    'payment_id' => $payId,
                    'reference_number' => 'REF-112233'
                ]
            ], 201)
        ]);

        $invoiceService = app(ZohoInvoiceService::class);
        $paymentData = $invoiceService->recordPayment($invId, 400.00, now()->format('Y-m-d'), 'bank_transfer');

        $this->assertEquals($payId, $paymentData['payment_id']);

        $invoice->refresh();
        $this->assertEquals(600.00, (float)$invoice->balance_due);
        $this->assertEquals(InvoiceStatusEnum::PARTIALLY_PAID, $invoice->status);

        $localPayment = Payment::where('zoho_payment_id', $payId)->first();
        $this->assertNotNull($localPayment);
        $this->assertEquals(400.00, (float)$localPayment->amount);
        $this->assertEquals(PaymentModeEnum::BANK_TRANSFER, $localPayment->payment_mode);
    }

    public function test_zoho_estimate_service_creates_manual_estimate(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $customer = User::withoutEvents(function () {
            return User::create([
                'name' => 'Alice Client',
                'email' => 'alice_' . Str::random(5) . '@example.com',
                'password' => bcrypt('password'),
                'zoho_contact_id' => 'zoho_contact_111',
            ]);
        });

        $estId = 'zoho_estimate_id_manual_' . Str::random(5);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/estimates?organization_id=test_org_id' => Http::response([
                'estimate' => [
                    'estimate_id' => $estId,
                    'estimate_number' => 'EST-1002'
                ]
            ], 201)
        ]);

        $estimateService = app(ZohoEstimateService::class);
        $result = $estimateService->createManualEstimate($customer, [
            [
                'name' => 'Manual Consulting Service',
                'rate' => 150.00,
                'quantity' => 10,
                'description' => '10 hours of consulting',
            ]
        ], [
            'valid_date' => '2026-06-30',
            'notes' => 'Custom manual note'
        ]);

        $this->assertEquals($estId, $result['estimate_id']);
        
        $localEstimate = Estimate::where('zoho_estimate_id', $estId)->first();
        $this->assertNotNull($localEstimate);
        $this->assertNull($localEstimate->rfq_id);
        $this->assertEquals($customer->id, $localEstimate->customer_id);
        $this->assertEquals(EstimateStatusEnum::DRAFT, $localEstimate->status);
        $this->assertEquals(1500.00, (float)$localEstimate->total);
    }

    public function test_zoho_estimate_service_gets_estimate_pdf(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/estimates/est_123?organization_id=test_org_id&accept=pdf' => Http::response('mocked_pdf_binary_content', 200)
        ]);

        $estimateService = app(ZohoEstimateService::class);
        $pdf = $estimateService->getEstimatePdf('est_123');

        $this->assertEquals('mocked_pdf_binary_content', $pdf);
    }

    public function test_zoho_invoice_service_creates_manual_invoice(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $customer = User::withoutEvents(function () {
            return User::create([
                'name' => 'Bob Finance',
                'email' => 'bob_' . Str::random(5) . '@example.com',
                'password' => bcrypt('password'),
                'zoho_contact_id' => 'zoho_contact_222',
            ]);
        });

        $invId = 'zoho_invoice_id_manual_' . Str::random(5);
        $invNum = 'INV-99901';

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/invoices?organization_id=test_org_id' => Http::response([
                'invoice' => [
                    'invoice_id' => $invId,
                    'invoice_number' => $invNum
                ]
            ], 201)
        ]);

        $invoiceService = app(ZohoInvoiceService::class);
        $result = $invoiceService->createManualInvoice($customer, [
            [
                'name' => 'Hardware Purchase',
                'rate' => 20.00,
                'quantity' => 50,
                'description' => '50 units of CAT6 cables',
            ]
        ], [
            'date' => '2026-05-21',
            'due_date' => '2026-06-04',
            'notes' => 'Invoice terms net 14'
        ]);

        $this->assertEquals($invId, $result['invoice_id']);
        
        $localInvoice = Invoice::where('zoho_invoice_id', $invId)->first();
        $this->assertNotNull($localInvoice);
        $this->assertNull($localInvoice->rfq_id);
        $this->assertNull($localInvoice->estimate_id);
        $this->assertEquals($customer->id, $localInvoice->customer_id);
        $this->assertEquals(InvoiceStatusEnum::DRAFT, $localInvoice->status);
        $this->assertEquals(1000.00, (float)$localInvoice->total);
    }

    public function test_zoho_invoice_service_gets_invoice_pdf(): void
    {
        ZohoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            'https://www.zohoapis.com/invoice/v3/invoices/inv_123?organization_id=test_org_id&accept=pdf' => Http::response('mocked_invoice_pdf_content', 200)
        ]);

        $invoiceService = app(ZohoInvoiceService::class);
        $pdf = $invoiceService->getInvoicePdf('inv_123');

        $this->assertEquals('mocked_invoice_pdf_content', $pdf);
    }

}

