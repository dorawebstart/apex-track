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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // restrict: сесію з бронюваннями не можна фізично видалити — лише архівувати
            $table->foreignId('track_session_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'track_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
