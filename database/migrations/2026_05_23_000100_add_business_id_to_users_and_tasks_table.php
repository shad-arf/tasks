<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('phone')->constrained()->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('archived_at')->constrained()->nullOnDelete();
        });

        $timestamp = now();

        $defaultBusinessId = DB::table('businesses')
            ->where('name', 'Default Business')
            ->value('id');

        if ($defaultBusinessId === null) {
            $defaultBusinessId = DB::table('businesses')->insertGetId([
                'name' => 'Default Business',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        DB::table('users')
            ->whereNull('business_id')
            ->update(['business_id' => $defaultBusinessId]);

        DB::table('tasks')
            ->whereNull('business_id')
            ->update(['business_id' => $defaultBusinessId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
        });
    }
};
