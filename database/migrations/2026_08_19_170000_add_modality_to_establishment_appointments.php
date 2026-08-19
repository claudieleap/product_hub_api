<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->string('modality', 20)->default('presencial')->after('time');
            $table->string('location', 255)->nullable()->after('modality');
            $table->string('payment_link', 500)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('establishment_appointments', function (Blueprint $table) {
            $table->dropColumn(['modality', 'location', 'payment_link']);
        });
    }
};
