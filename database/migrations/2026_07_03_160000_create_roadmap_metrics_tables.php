<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_metric_groups', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('label', 200);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('roadmap_metrics', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('group_id', 80)->index();
            $table->string('label', 200);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('group_id')
                ->references('id')
                ->on('roadmap_metric_groups')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_metrics');
        Schema::dropIfExists('roadmap_metric_groups');
    }
};
