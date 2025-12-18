<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metodos_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->enum('taxa_tipo', ['N/A', 'DESCONTAR', 'ACRESCENTAR'])->default('N/A');
            $table->decimal('taxa_percentual', 8, 2)->default(0);
            $table->enum('descricao_nfe', ['cash', 'cheque', 'creditCard', 'debitCard', 'storeCredict', 'foodVouchers', 'mealVouchers', 'giftVouchers', 'fuelVouchers', 'bankBill', 'withoutPayment', 'InstantPayment', 'others'])->nullable('others');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('metodos_pagamentos')->insert([
            [
                'nome' => 'Dinheiro',
                'taxa_tipo' => 'N/A',
                'taxa_percentual' => 0,
                'descricao_nfe' => 'cash',
            ],
            [
                'nome' => 'Cartão de Crédito',
                'taxa_tipo' => 'ACRESCENTAR',
                'taxa_percentual' => 0,
                'descricao_nfe' => 'creditCard',
            ],
            [
                'nome' => 'Cartão de Débito',
                'taxa_tipo' => 'N/A',
                'taxa_percentual' => 0,
                'descricao_nfe' => 'debitCard',
            ],
            [
                'nome' => 'PIX',
                'taxa_tipo' => 'N/A',
                'taxa_percentual' => 0,
                'descricao_nfe' => 'InstantPayment',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_pagamentos');
    }
};