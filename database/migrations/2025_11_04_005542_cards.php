<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('image_path')->nullable();
            $table->unsignedTinyInteger('stock');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('level');
            $table->unsignedTinyInteger('version');
            $table->unsignedTinyInteger('type_id');

            $table->foreignId('node_id')
                ->nullable()
                ->constrained('nodes')
                ->nullOnDelete();

            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('card_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
