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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('pm_id')->constrained('users')->onDelete('restrict');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('status')->default('Planning');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Add PostgreSQL constraints
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('Planning', 'On Going', 'Completed', 'Cancelled'))");
        \Illuminate\Support\Facades\DB::statement("CREATE INDEX projects_active_idx ON projects(deleted_at) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
