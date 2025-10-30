<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bandeira_cartao_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->enum('bandeira', ['None', 'Visa', 'Mastercard', 'AmericanExpress', 'Sorocred', 'DinersClub', 'Elo', 'Hipercard', 'Aura', 'Cabal', 'Alelo', 'BanesCard', 'CalCard', 'Credz', 'Discover', 'GoodCard', 'GreenCard', 'Hiper', 'JCB', 'Mais', 'MaxVan', 'Policard', 'RedeCompras', 'Sodexo', 'ValeCard', 'Verocheque', 'VR', 'Ticket', 'Other']);
            $table->string('cnpj_crendeciador')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandeira_cartao_pagamentos');
    }
};
