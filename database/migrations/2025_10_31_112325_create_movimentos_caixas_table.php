<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('movimentos_caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas');
            $table->foreignId('aluguel_id')->nullable()->constrained('alugueis');
            $table->foreignId('user_id')->constrained('users');
            $table->string('descricao')->nullable();
            $table->enum('tipo', ['entrada', 'saida'])->default('entrada');
            $table->foreignId('metodo_pagamento_id')->constrained('metodos_pagamentos');
            $table->foreignId('cartao_pagamento_id')->nullable()->constrained('cartoes_pagamento');
            $table->string('autorizacao')->nullable();
            $table->decimal('valor_pago', 10, 2);
            $table->decimal('valor_recebido', 10, 2)->default(0);
            $table->decimal('valor_acrescimo', 10, 2)->default(0);
            $table->decimal('valor_desconto', 10, 2)->default(0);
            $table->decimal('troco', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->dateTime('data_movimento');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimento_caixas');
    }
};
