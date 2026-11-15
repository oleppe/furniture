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
        Schema::create('speed_telemetry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->decimal('ttft_ms', 10, 2)->nullable();
            $table->decimal('tokens_per_sec', 10, 2)->nullable();
            $table->timestamp('tested_at');
            $table->string('region')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speed_telemetry');
    }
};
