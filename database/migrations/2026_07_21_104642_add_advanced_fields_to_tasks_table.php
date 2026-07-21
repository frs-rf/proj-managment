<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_code')->nullable()->unique()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->text('acceptance_criteria')->nullable()->after('description');
            
            $table->foreignId('reporter_id')->nullable()->after('assigned_to')->constrained('users')->onDelete('set null');
            $table->foreignId('reviewer_id')->nullable()->after('reporter_id')->constrained('users')->onDelete('set null');
            $table->json('watchers')->nullable()->after('reviewer_id');
            
            $table->string('priority')->default('Medium')->after('status');
            $table->string('task_type')->default('New Feature')->after('priority');
            $table->string('module')->nullable()->after('task_type');
            $table->json('tags')->nullable()->after('module');
            
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('end_date');
            $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['reporter_id']);
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn([
                'task_code', 'description', 'acceptance_criteria', 
                'reporter_id', 'reviewer_id', 'watchers', 
                'priority', 'task_type', 'module', 'tags', 
                'estimated_hours', 'actual_hours'
            ]);
        });
    }
};
