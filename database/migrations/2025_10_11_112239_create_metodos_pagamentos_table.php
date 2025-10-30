<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metodos_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->enum('taxa_tipo', ['N/A', 'DESCONTAR','ACRESCENTAR'])->default('N/A');
            $table->decimal('taxa_percentual', 8, 2)->default(0);
            $table->enum('descricao_nfe',['cash','cheque','creditCard','debitCard','storeCredict','foodVouchers','mealVouchers','giftVouchers','fuelVouchers','bankBill','withoutPayment','InstantPayment','others'])->nullable('others');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_pagamentos');
    }
};
