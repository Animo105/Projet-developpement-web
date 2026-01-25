<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('card_types', function (Blueprint $table) {
            $table->tinyInteger('id')->unsigned()->autoIncrement()->primary();
            $table->string('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
