<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->decimal('pay_price');
            $table->date('date');
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('tarif_id')->constrained('tarifs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_locations');
    }
};
