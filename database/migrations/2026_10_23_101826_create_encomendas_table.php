<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encomendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->unsignedInteger('total_centimos');
            $table->string('estado')->default('pendente');
            $table->string('estado_pagamento')->default('pendente');
            $table->string('nome_destinatario');
            $table->string('morada');
            $table->string('codigo_postal');
            $table->string('cidade');
            $table->string('pais')->default('Portugal');
            $table->string('telefone')->nullable();
            $table->string('stripe_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encomendas');
    }
};