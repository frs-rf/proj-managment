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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('weight', 5, 2); // percentage up to 100.00
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('To Do');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('To Do', 'In Progress', 'Review', 'Done'))");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_weight_check CHECK (weight >= 0 AND weight <= 100)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
