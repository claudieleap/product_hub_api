<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildTable('roadmap_items', function (Blueprint $table) {
            $table->string('roadmap_type', 20);
            $table->string('id', 120);
            $table->primary(['roadmap_type', 'id']);
            $table->string('product_id', 80);
            $table->string('priority', 20);
            $table->string('title', 500);
            $table->text('notes')->default('');
            $table->json('metrics')->nullable();
            $table->string('dev_status', 30)->nullable();
            $table->timestamp('item_created_at')->nullable();
            $table->timestamps();
            $table->index(['roadmap_type', 'product_id']);
            $table->index(['roadmap_type', 'priority']);
            $table->index(['roadmap_type', 'dev_status']);
        }, [
            ['roadmap_type', "'saas'"],
            'id', 'product_id', 'priority', 'title', 'notes', 'metrics', 'dev_status', 'item_created_at', 'created_at', 'updated_at',
        ]);

        $this->rebuildTable('roadmap_custom_products', function (Blueprint $table) {
            $table->string('roadmap_type', 20);
            $table->string('id', 80);
            $table->primary(['roadmap_type', 'id']);
            $table->string('title', 200);
            $table->text('description')->default('');
            $table->string('icon', 80)->default('pi pi-box');
            $table->string('accent', 20)->default('#1e4fe0');
            $table->timestamp('product_created_at')->nullable();
            $table->timestamps();
        }, [
            ['roadmap_type', "'saas'"],
            'id', 'title', 'description', 'icon', 'accent', 'product_created_at', 'created_at', 'updated_at',
        ]);

        $this->rebuildTable('roadmap_deleted_seeds', function (Blueprint $table) {
            $table->string('roadmap_type', 20);
            $table->string('seed_id', 120);
            $table->primary(['roadmap_type', 'seed_id']);
            $table->timestamps();
        }, [
            ['roadmap_type', "'saas'"],
            'seed_id', 'created_at', 'updated_at',
        ]);
    }

    public function down(): void
    {
        $this->rebuildTable('roadmap_items', function (Blueprint $table) {
            $table->string('id', 120)->primary();
            $table->string('product_id', 80)->index();
            $table->string('priority', 20)->index();
            $table->string('title', 500);
            $table->text('notes')->default('');
            $table->json('metrics')->nullable();
            $table->string('dev_status', 30)->nullable()->index();
            $table->timestamp('item_created_at')->nullable();
            $table->timestamps();
        }, ['id', 'product_id', 'priority', 'title', 'notes', 'metrics', 'dev_status', 'item_created_at', 'created_at', 'updated_at'], true);

        $this->rebuildTable('roadmap_custom_products', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('title', 200);
            $table->text('description')->default('');
            $table->string('icon', 80)->default('pi pi-box');
            $table->string('accent', 20)->default('#1e4fe0');
            $table->timestamp('product_created_at')->nullable();
            $table->timestamps();
        }, ['id', 'title', 'description', 'icon', 'accent', 'product_created_at', 'created_at', 'updated_at'], true);

        $this->rebuildTable('roadmap_deleted_seeds', function (Blueprint $table) {
            $table->string('seed_id', 120)->primary();
            $table->timestamps();
        }, ['seed_id', 'created_at', 'updated_at'], true);
    }

    /**
     * @param  list<string|array{0: string, 1: string}>  $columns
     */
    private function rebuildTable(string $table, callable $schema, array $columns, bool $stripType = false): void
    {
        $legacy = "{$table}_legacy";

        Schema::rename($table, $legacy);
        Schema::create($table, $schema);

        $targetColumns = [];
        $selectParts = [];

        foreach ($columns as $column) {
            if (is_array($column)) {
                [$target, $expression] = $column;
                $targetColumns[] = $target;
                $selectParts[] = DB::raw("{$expression} as {$target}");
                continue;
            }

            if ($stripType && $column === 'roadmap_type') {
                continue;
            }

            $targetColumns[] = $column;
            $selectParts[] = $column;
        }

        $rows = DB::table($legacy)->select($selectParts)->get();

        if ($rows->isNotEmpty()) {
            DB::table($table)->insert(
                $rows->map(fn ($row) => (array) $row)->all()
            );
        }

        Schema::drop($legacy);
    }
};
