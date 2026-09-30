<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('user')->after('email');
            });
        }

        if (!Schema::hasTable('editoras')) {
            Schema::create('editoras', function (Blueprint $table) {
                $table->id();
                $table->text('nome');
                $table->text('logotipo')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('autores')) {
            Schema::create('autores', function (Blueprint $table) {
                $table->id();
                $table->text('nome');
                $table->text('foto')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('livros')) {
            Schema::create('livros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('editora_id')->constrained('editoras')->cascadeOnDelete();
                $table->text('isbn');
                $table->text('nome');
                $table->longText('bibliografia')->nullable();
                $table->text('imagem_capa')->nullable();
                $table->text('preco');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('autor_livro')) {
            Schema::create('autor_livro', function (Blueprint $table) {
                $table->id();
                $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
                $table->foreignId('autor_id')->constrained('autores')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('autor_livro');
        Schema::dropIfExists('livros');
        Schema::dropIfExists('autores');
        Schema::dropIfExists('editoras');

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};