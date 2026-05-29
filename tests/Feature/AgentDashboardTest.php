<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethodEnum;
use App\Enums\QuantityEnum;
use App\Models\Category;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AgentDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        User::unsetEventDispatcher();
    }

    public function test_agent_dashboard_shows_ai_summary(): void
    {
        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        
        $agent = User::create([
            'name' => 'Test Agent',
            'email' => 'agent_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);
        $agent->assignRole($agentRole);

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
            'assigned_agent_id' => $agent->id,
            'product_name' => 'Custom PCBs',
            'specifications' => '2 layers, 1.6mm thickness',
            'quantity' => QuantityEnum::ONE_TO_TEN,
            'delivery_method' => DeliveryMethodEnum::AIR,
            'target_price' => '$5 per unit',
            'additional_requirements' => 'Lead-free finish',
            'location' => 'Lagos, Nigeria',
            'company_name' => 'Doe Tech Ltd',
            'tracking_token' => Str::uuid()->toString(),
            'ai_summary' => 'This is a mocked AI summary from Claude.',
            'ai_suppliers' => [['company_name' => 'Mock Supplier']],
        ]);

        $response = $this->actingAs($agent)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Dashboard')
            ->has('rfqs', 1)
            ->where('rfqs.0.ai_summary', 'This is a mocked AI summary from Claude.')
            ->has('rfqs.0.ai_suppliers', 1)
        );
    }
}
