<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishment_units', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('establishment_id', 40)->index();
            $table->string('name', 120);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->foreign('establishment_id')->references('id')->on('establishments')->cascadeOnDelete();
        });

        Schema::create('establishment_convenios', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('establishment_id', 40)->index();
            $table->string('name', 120);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->foreign('establishment_id')->references('id')->on('establishments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_convenios');
        Schema::dropIfExists('establishment_units');
    }
};
