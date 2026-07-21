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
        Schema::create('task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->decimal('progress_percent', 5, 2); // 0 to 100
            $table->date('report_date');
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE task_progress ADD CONSTRAINT tp_percent_check CHECK (progress_percent >= 0 AND progress_percent <= 100)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_progress');
    }
};
