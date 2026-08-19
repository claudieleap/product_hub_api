<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishment_comments', function (Blueprint $table) {
            $table->id();
            $table->string('establishment_id', 40)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name', 255);
            $table->text('comment');
            $table->timestamps();

            $table->foreign('establishment_id')->references('id')->on('establishments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_comments');
    }
};
