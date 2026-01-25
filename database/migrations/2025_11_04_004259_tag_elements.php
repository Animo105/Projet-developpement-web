<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tag_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
            $table->foreignId('element_id')->constrained('elements')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_elements');
    }
};
