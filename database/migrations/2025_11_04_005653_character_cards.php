<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('character_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained("cards")->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->boolean('is_owned')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_cards');
    }
};
