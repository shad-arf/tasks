<?php

use App\Models\Task;
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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('priority')->default(Task::PRIORITY_HIGH)->after('description');
            $table->date('due_date')->nullable()->after('priority');
            $table->string('status')->default(Task::STATUS_PENDING)->after('due_date');
        });

        DB::table('tasks')
            ->where('is_completed', true)
            ->update(['status' => Task::STATUS_COMPLETED]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['priority', 'due_date', 'status']);
        });
    }
};
