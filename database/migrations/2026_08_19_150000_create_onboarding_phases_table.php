<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fases (colunas) do board de Onboarding. O time pode renomear/reordenar/criar
 * novas fases, então isso mora no banco (não é um enum fixo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_phases', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('title', 120);
            $table->string('hint', 120)->default('');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('onboarding_phases')->insert([
            ['id' => 'backlog', 'title' => 'Backlog', 'hint' => 'A implantar', 'order_index' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'onboarding', 'title' => 'Onboarding', 'hint' => 'Em implantação', 'order_index' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'golive', 'title' => 'Go live', 'hint' => 'Ongoing', 'order_index' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_phases');
    }
};
