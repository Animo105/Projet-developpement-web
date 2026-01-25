<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('element_element', function (Blueprint $table) {
            $table->foreignId('element_id')->constrained('elements')->cascadeOnDelete();
            $table->foreignId('another_element_id')->constrained('elements')->cascadeOnDelete();

            $table->primary(['element_id', 'another_element_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('element_element');
    }
};
