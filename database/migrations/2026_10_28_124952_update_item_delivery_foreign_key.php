<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encomenda_items', function (Blueprint $table) {
            $table->dropForeign(['livro_id']);
            $table->foreign('livro_id')
                ->references('id')
                ->on('livros')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('encomenda_items', function (Blueprint $table) {
            $table->dropForeign(['livro_id']);
            $table->foreign('livro_id')
                ->references('id')
                ->on('livros')
                ->cascadeOnDelete();
        });
    }
};