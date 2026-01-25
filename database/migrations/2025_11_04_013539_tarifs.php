<?php

use App\Enums\TarifType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('type', TarifType::values());
            $table->decimal('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
