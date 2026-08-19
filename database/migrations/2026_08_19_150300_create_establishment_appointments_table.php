<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishment_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('establishment_id', 40)->index();
            $table->date('date')->index();
            $table->string('time', 5);
            $table->string('responsavel_id', 40)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('establishment_id')->references('id')->on('establishments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_appointments');
    }
};
