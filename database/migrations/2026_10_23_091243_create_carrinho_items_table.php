<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrinho_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
            $table->unsignedInteger('quantidade')->default(1);
            $table->timestamp('aviso_abandono_enviado_em')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'livro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrinho_items');
    }
};