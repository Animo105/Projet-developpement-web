<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('icon_id');
            $table->timestamps();

            $table->foreign('icon_id')->references('id')->on('icons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elements');
    }
};
