<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'is_branch')) {
                $table->boolean('is_branch')->default(false)->after('name');
            }
            if (!Schema::hasColumn('warehouses', 'phone')) {
                $table->string('phone')->nullable()->after('is_branch');
            }
            if (!Schema::hasColumn('warehouses', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['is_branch', 'phone', 'address']);
        });
    }
};
