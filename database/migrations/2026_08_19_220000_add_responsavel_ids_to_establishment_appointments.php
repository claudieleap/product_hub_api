<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agendamento passa a aceitar mais de um responsável — responsavel_id (string
 * única) vira responsavel_ids (json array), preservando o valor único que já
 * existia em cada linha.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->json('responsavel_ids')->nullable()->after('responsavel_id');
        });

        DB::table('establishment_appointments')->whereNotNull('responsavel_id')->orderBy('id')
            ->each(function ($row) {
                DB::table('establishment_appointments')->where('id', $row->id)
                    ->update(['responsavel_ids' => json_encode([$row->responsavel_id])]);
            });

        DB::table('establishment_appointments')->whereNull('responsavel_ids')
            ->update(['responsavel_ids' => json_encode([])]);

        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->dropColumn('responsavel_id');
        });
    }

    public function down(): void
    {
        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->string('responsavel_id', 40)->nullable()->after('payment_link');
        });

        DB::table('establishment_appointments')->orderBy('id')->each(function ($row) {
            $ids = json_decode($row->responsavel_ids ?? '[]', true) ?: [];
            DB::table('establishment_appointments')->where('id', $row->id)
                ->update(['responsavel_id' => $ids[0] ?? null]);
        });

        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->dropColumn('responsavel_ids');
        });
    }
};
