<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('is_completed')->default(false);

        // ئەو کەسەی تاسکەکەی داناوە
        $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');

        // ئەو کەسەی تاسکەکەی پێ سپێردراوە
        $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
