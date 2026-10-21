<?php

namespace Tests\Feature;

use App\Models\Crm\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_list_page_loads(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/app/crm/leads');
        $response->assertStatus(200);
    }

    public function test_can_create_lead(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/app/crm/leads', [
            'name' => '测试客户公司',
            'contact_person' => '张三',
            'phone' => '13800138000',
            'source' => 'website',
        ]);

        $response->assertRedirect('/app/crm/leads');
        $this->assertDatabaseHas('crm_leads', ['name' => '测试客户公司']);
    }

    public function test_lead_show_page_loads(): void
    {
        $user = User::factory()->create();
        $lead = Lead::create([
            'name' => '测试线索',
            'source' => 'manual',
            'created_by' => $user->id,
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/app/crm/leads/{$lead->id}");
        $response->assertStatus(200);
    }

    public function test_pipeline_page_loads(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/app/crm/pipeline');
        $response->assertStatus(200);
    }
}
