<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('amount');
            $table->string('note')->nullable();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->unsignedTinyInteger('method_id');

            $table->foreign('method_id')->references('id')->on('methods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
