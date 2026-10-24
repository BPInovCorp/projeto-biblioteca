<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encomenda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_id')->constrained('encomendas')->cascadeOnDelete();
            $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
            $table->unsignedInteger('quantidade');
            $table->unsignedInteger('preco_unitario_centimos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encomenda_items');
    }
};