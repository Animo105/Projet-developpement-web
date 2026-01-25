<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->tinyInteger('id')->unsigned()->autoIncrement()->primary();
            $table->string('name');
            $table->string('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
