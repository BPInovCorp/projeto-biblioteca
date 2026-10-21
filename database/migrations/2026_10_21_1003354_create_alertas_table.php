<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
            $table->timestamp('notificado_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'livro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};