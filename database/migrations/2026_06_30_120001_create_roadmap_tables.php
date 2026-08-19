<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_items', function (Blueprint $table) {
            $table->string('id', 120)->primary();
            $table->string('product_id', 80)->index();
            $table->string('priority', 20)->index();
            $table->string('title', 500);
            $table->text('notes')->default('');
            $table->json('metrics')->nullable();
            $table->string('dev_status', 30)->nullable()->index();
            $table->timestamp('item_created_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roadmap_custom_products', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('title', 200);
            $table->text('description')->default('');
            $table->string('icon', 80)->default('pi pi-box');
            $table->string('accent', 20)->default('#1e4fe0');
            $table->timestamp('product_created_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roadmap_deleted_seeds', function (Blueprint $table) {
            $table->string('seed_id', 120)->primary();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_deleted_seeds');
        Schema::dropIfExists('roadmap_custom_products');
        Schema::dropIfExists('roadmap_items');
    }
};
