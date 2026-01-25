<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('x');
            $table->tinyInteger('y');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('nodes')
                ->cascadeOnDelete();

            $table->foreignId('locked_by_id')
                ->nullable()
                ->constrained('nodes')
                ->nullOnDelete();

            $table->foreignId('element_id')
                ->nullable()
                ->constrained('elements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
