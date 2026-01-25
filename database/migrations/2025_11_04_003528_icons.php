e<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('icons', function (Blueprint $table) {
            $table->tinyInteger('id')->unsigned()->autoIncrement()->primary();
            $table->string('name');
            $table->string('path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icons');
    }
};
