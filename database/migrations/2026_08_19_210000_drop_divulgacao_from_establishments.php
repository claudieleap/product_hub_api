<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campo "Divulgação" removido do card — não era usado em nenhuma decisão
 * do funil nem do dashboard, só ruído a mais no formulário.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('divulgacao');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->string('divulgacao', 40)->nullable()->after('grupo_econ');
        });
    }
};
