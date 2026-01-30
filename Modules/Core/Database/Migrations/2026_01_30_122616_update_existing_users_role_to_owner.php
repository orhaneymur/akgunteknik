<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing users with 'admin' role to 'owner'
        // Also update users with null role to 'owner' (first user should be owner)
        DB::table('users')
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereNull('role');
            })
            ->update(['role' => 'owner']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert owner back to admin (if needed)
        // Note: This is a one-way migration in practice
        DB::table('users')
            ->where('role', 'owner')
            ->update(['role' => 'admin']);
    }
};
