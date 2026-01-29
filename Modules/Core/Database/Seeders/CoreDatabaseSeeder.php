<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Golden Tenant data.
     */
    /**
     * Seed the application's database with Tenant data.
     */
    public function run(): void
    {
        // Start transaction for data integrity
        DB::beginTransaction();

        try {
            // 1. Create Tenant: "Orhan Teknik"
            $this->createTenant(
                'Orhan Teknik',
                'orhanteknik',
                'Orhan Admin',
                'admin@orhanteknik.com',
                'password'
            );

            // 2. Create Tenant: "Akgün Teknik"
            $this->createTenant(
                'Akgün Teknik',
                'akgunteknik',
                'Akgün Admin',
                'admin@akgunteknik.com',
                'password'
            );

            DB::commit();
            $this->command->info("\n✅ All tenants seeded successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error seeding database: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper to create a full tenant setup.
     */
    private function createTenant(string $companyName, string $domainPrefix, string $adminName, string $adminEmail, string $password): void
    {
        // Check if tenant exists to avoid duplicates
        $existingTenant = DB::table('tenants')->where('domain_prefix', $domainPrefix)->first();
        if ($existingTenant) {
            $this->command->warn("⚠️ Tenant '{$companyName}' already exists. Skipping...");
            return;
        }

        // 1. Create Tenant
        $tenantId = DB::table('tenants')->insertGetId([
            'company_name' => $companyName,
            'domain_prefix' => $domainPrefix,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Tenant created: {$companyName} (ID: {$tenantId})");

        // 2. Create Warehouse
        $warehouseId = DB::table('warehouses')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Merkez Depo',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Branch
        $branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $tenantId,
            'warehouse_id' => $warehouseId,
            'name' => 'Merkez Şube',
            'address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create Admin User
        $userId = DB::table('users')->insertGetId([
            'name' => $adminName,
            'email' => $adminEmail,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("  User created: {$adminName} ({$adminEmail})");
    }
}

