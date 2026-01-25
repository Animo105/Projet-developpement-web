<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->boolean('is_xp');
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
