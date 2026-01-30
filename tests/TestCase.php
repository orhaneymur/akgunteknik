<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\Core\Models\Tenant;
use Modules\Core\Models\Warehouse;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create a test user with tenant and warehouse
     */
    protected function createTestUser($role = 'owner')
    {
        // Create tenant
        $tenant = Tenant::create([
            'company_name' => 'Test Company',
            'domain_prefix' => 'test-' . uniqid(),
            'is_active' => true,
        ]);

        // Create warehouse
        $warehouse = Warehouse::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Warehouse',
            'is_active' => true,
        ]);

        // Create user
        return User::create([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'role' => $role,
        ]);
    }

    /**
     * Get auth token for test user
     */
    protected function getAuthToken(User $user)
    {
        return $user->createToken('test-token')->plainTextToken;
    }
}
