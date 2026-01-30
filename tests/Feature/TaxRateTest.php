<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Core\Models\TaxRate;
use App\Models\User;

class TaxRateTest extends TestCase
{
    public function test_manager_can_create_tax_rate()
    {
        $user = $this->createTestUser('manager');
        $token = $this->getAuthToken($user);

        $response = $this->postJson('/api/core/tax-rates', [
            'name' => 'KDV %20',
            'rate' => 20.00,
            'is_active' => true,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'rate',
                    'is_active',
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tax_rates', [
            'name' => 'KDV %20',
            'rate' => 20.00,
            'tenant_id' => $user->tenant_id,
        ]);
    }

    public function test_staff_cannot_create_tax_rate()
    {
        $user = $this->createTestUser('staff');
        $token = $this->getAuthToken($user);

        $response = $this->postJson('/api/core/tax-rates', [
            'name' => 'KDV %20',
            'rate' => 20.00,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_list_tax_rates()
    {
        $user = $this->createTestUser('staff');
        $token = $this->getAuthToken($user);

        // Create some tax rates
        TaxRate::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'KDV %1',
            'rate' => 1.00,
            'is_active' => true,
        ]);

        TaxRate::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'KDV %20',
            'rate' => 20.00,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/core/tax-rates', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'rate',
                    ],
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertCount(2, $response->json('data'));
    }
}
