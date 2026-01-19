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
    public function run(): void
    {
        // Start transaction for data integrity
        DB::beginTransaction();

        try {
            // 1. Create Tenant: "Orhan Teknik"
            $tenant = DB::table('tenants')->insertGetId([
                'company_name' => 'Orhan Teknik',
                'domain_prefix' => 'orhanteknik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ Tenant created: Orhan Teknik (ID: {$tenant})");

            // 2. Create Warehouse: "Merkez Depo"
            $warehouse = DB::table('warehouses')->insertGetId([
                'tenant_id' => $tenant,
                'name' => 'Merkez Depo',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ Warehouse created: Merkez Depo (ID: {$warehouse})");

            // 3. Create Branch: "Merkez Şube" (linked to warehouse)
            $branch = DB::table('branches')->insertGetId([
                'tenant_id' => $tenant,
                'warehouse_id' => $warehouse,
                'name' => 'Merkez Şube',
                'address' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ Branch created: Merkez Şube (ID: {$branch})");

            // 4. Create Admin User
            $userId = DB::table('users')->insertGetId([
                'name' => 'Orhan Admin',
                'email' => 'admin@orhanteknik.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'tenant_id' => $tenant,
                'branch_id' => $branch,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ Admin user created: Orhan Admin (ID: {$userId})");
            $this->command->info("  Email: admin@orhanteknik.com");
            $this->command->info("  Password: password");

            DB::commit();
            $this->command->info("\n✅ Golden Tenant seeding completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error seeding database: " . $e->getMessage());
            throw $e;
        }
    }
}

