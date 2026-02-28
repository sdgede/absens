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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->enum('type', ['checkin', 'checkout']);
            $table->enum('status', ['present', 'late', 'absent', 'leave', 'sick', 'holiday']);
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->decimal('face_confidence', 5, 4)->nullable();
            $table->decimal('liveness_score', 5, 4)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'session_id', 'type'], 'user_session_type_unique');
            $table->index(['user_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
