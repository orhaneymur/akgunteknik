<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Inventory\Models\Product;
use App\Models\User;

class ProductTest extends TestCase
{
    public function test_authenticated_user_can_create_product()
    {
        $user = $this->createTestUser();
        $token = $this->getAuthToken($user);

        $response = $this->postJson('/api/inventory/products', [
            'name' => 'Test Product',
            'base_price' => 100.00,
            'cost_price' => 50.00,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'base_price',
                    'tenant_id',
                ],
                'message',
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'tenant_id' => $user->tenant_id,
        ]);
    }

    public function test_product_creation_requires_authentication()
    {
        $response = $this->postJson('/api/inventory/products', [
            'name' => 'Test Product',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_only_see_own_tenant_products()
    {
        $user1 = $this->createTestUser();
        $user2 = $this->createTestUser();

        // Create product for user1's tenant
        Product::create([
            'tenant_id' => $user1->tenant_id,
            'name' => 'User1 Product',
            'sku' => 'PRD-001',
            'base_price' => 100,
            'cost_price' => 50,
            'is_active' => true,
        ]);

        // Create product for user2's tenant
        Product::create([
            'tenant_id' => $user2->tenant_id,
            'name' => 'User2 Product',
            'sku' => 'PRD-002',
            'base_price' => 200,
            'cost_price' => 100,
            'is_active' => true,
        ]);

        $token = $this->getAuthToken($user1);
        $response = $this->getJson('/api/inventory/products?all=1', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $products = $response->json('data');
        $this->assertCount(1, $products);
        $this->assertEquals('User1 Product', $products[0]['name']);
    }
}
