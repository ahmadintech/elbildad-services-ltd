<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethodEnum;
use App\Enums\QuantityEnum;
use App\Jobs\ProcessRfqWithAI;
use App\Models\Category;
use App\Models\Rfq;
use App\Models\User;
use App\Services\Ai\ClaudeAiService;
use App\Services\Mail\RfqMailService;
use App\Mail\RfqSummaryMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClaudeIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'mock-api-key']);
        
        // Disable Eloquent events for Claude AI testing to ignore Zoho sync observers
        User::unsetEventDispatcher();
    }

    public function test_claude_ai_service_successfully_enriches_rfq(): void
    {
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'john_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::firstOrCreate([
            'slug' => 'electronics'
        ], [
            'name' => 'Electronics'
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers, 1.6mm thickness',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'target_price' => '$5 per unit',
            'additional_requirements' => 'Lead-free finish',
            'location' => 'Lagos, Nigeria',
            'company_name' => 'Doe Tech Ltd',
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $mockResponse = [
            'content' => [
                [
                    'type' => 'text',
                    'text' => '```json
{
  "summary": "Customer needs custom lead-free PCBs in Lagos, Nigeria.",
  "suppliers": [
    {
      "company_name": "Shenzhen PCB Corp",
      "location": "Shenzhen, Guangdong, China",
      "specialization": "Rigid PCBs",
      "estimated_moq": "5 pcs",
      "contact_hint": "Search on Alibaba",
      "why_recommended": "High rating"
    },
    {
      "company_name": "Guangzhou Electronics Ltd",
      "location": "Guangzhou, Guangdong, China",
      "specialization": "Flexible PCBs",
      "estimated_moq": "10 pcs",
      "contact_hint": "Made-in-China",
      "why_recommended": "Competitive pricing"
    },
    {
      "company_name": "Dongguan Precision Co.",
      "location": "Dongguan, Guangdong, China",
      "specialization": "Multilayer PCBs",
      "estimated_moq": "1 pc",
      "contact_hint": "Global Sources",
      "why_recommended": "No minimum order"
    }
  ]
}
```'
                ]
            ]
        ];

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response($mockResponse, 200)
        ]);

        $service = new ClaudeAiService();
        $result = $service->enrichRfq($rfq);

        $this->assertEquals("Customer needs custom lead-free PCBs in Lagos, Nigeria.", $result['ai_summary']);
        $this->assertCount(3, $result['ai_suppliers']);
        $this->assertEquals("Shenzhen PCB Corp", $result['ai_suppliers'][0]['company_name']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->header('x-api-key')[0] === 'mock-api-key'
                && $request->header('anthropic-version')[0] === '2023-06-01'
                && str_contains($request->body(), 'Custom PCBs');
        });
    }

    public function test_claude_ai_service_handles_api_failure_gracefully(): void
    {
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'john_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response('Internal Server Error', 500)
        ]);

        $service = new ClaudeAiService();
        $result = $service->enrichRfq($rfq);

        $this->assertEquals('', $result['ai_summary']);
        $this->assertEquals([], $result['ai_suppliers']);
    }

    public function test_claude_ai_service_handles_invalid_json_gracefully(): void
    {
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'john_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $mockResponse = [
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'This is not valid JSON string at all'
                ]
            ]
        ];

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response($mockResponse, 200)
        ]);

        $service = new ClaudeAiService();
        $result = $service->enrichRfq($rfq);

        $this->assertEquals('', $result['ai_summary']);
        $this->assertEquals([], $result['ai_suppliers']);
    }

    public function test_process_rfq_job_coordinates_enrichment_and_mail(): void
    {
        Mail::fake();

        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'john_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $enrichmentData = [
            'ai_summary' => 'Analyzed summary',
            'ai_suppliers' => [
                ['company_name' => 'Supplier A', 'location' => 'China']
            ]
        ];

        $claudeServiceMock = $this->createMock(ClaudeAiService::class);
        $claudeServiceMock->expects($this->once())
            ->method('enrichRfq')
            ->with($this->callback(function ($passedRfq) use ($rfq) {
                return $passedRfq->id === $rfq->id;
            }))
            ->willReturn($enrichmentData);

        $mailService = new RfqMailService();

        $job = new ProcessRfqWithAI($rfq);
        $job->handle($claudeServiceMock, $mailService);

        $rfq->refresh();
        $this->assertEquals('Analyzed summary', $rfq->ai_summary);
        $this->assertEquals([['company_name' => 'Supplier A', 'location' => 'China']], $rfq->ai_suppliers);

        Mail::assertQueued(RfqSummaryMail::class, function ($mail) use ($customer, $rfq) {
            return $mail->hasTo($customer->email) && $mail->rfq->id === $rfq->id;
        });
    }

    public function test_mail_service_skips_whatsapp_only_users(): void
    {
        Mail::fake();

        $customer = User::create([
            'name' => 'John Doe',
            'whatsapp_number' => '+2348000000000',
            'email' => null,
            'password' => bcrypt('password'),
        ]);

        $rfq = Rfq::create([
            'customer_id' => $customer->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'tracking_token' => Str::uuid()->toString(),
        ]);

        $mailService = new RfqMailService();
        $mailService->sendRfqSummaryToCustomer($rfq);

        Mail::assertNothingQueued();
    }
}
