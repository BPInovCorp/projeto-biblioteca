<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('livro_id')->constrained('livros')->restrictOnDelete();
            $table->foreignId('requisicao_id')->unique()->constrained('requisicoes')->restrictOnDelete();
            $table->unsignedTinyInteger('classificacao');
            $table->text('comentario');
            $table->string('status', 20)->default('suspenso');
            $table->text('justificacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};