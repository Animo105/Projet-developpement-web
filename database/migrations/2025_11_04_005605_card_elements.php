<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('card_elements', function (Blueprint $table) {
            $table->tinyInteger('id')->unsigned()->autoIncrement()->primary();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('element_id');
            $table->unsignedTinyInteger('type_id');
            $table->unsignedInteger('amount');

            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->foreign('element_id')->references('id')->on('elements')->cascadeOnDelete();
            $table->foreign('type_id')->references('id')->on('card_element_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_elements');
    }
};
