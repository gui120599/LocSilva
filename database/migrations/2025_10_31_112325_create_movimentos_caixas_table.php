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
            $table->foreignId('caixa_id')->nullable()->constrained('caixas');
            $table->foreignId('aluguel_id')->nullable()->constrained('alugueis');
            $table->foreignId('user_id')->constrained('users');
            $table->string('descricao')->nullable();
            $table->enum('tipo', ['entrada', 'saida'])->default('entrada');
            $table->foreignId('metodo_pagamento_id')->constrained('metodos_pagamentos');
            $table->foreignId('cartao_pagamento_id')->nullable()->constrained('bandeira_cartao_pagamentos');
            $table->string('autorizacao')->nullable();
            $table->decimal('valor_pago_movimento', 10, 2)->default(0);
            $table->decimal('valor_recebido_movimento', 10, 2)->default(0);
            $table->decimal('valor_acrescimo_movimento', 10, 2)->default(0);
            $table->decimal('valor_desconto_movimento', 10, 2)->default(0);
            $table->decimal('troco_movimento', 10, 2)->default(0);
            $table->decimal('valor_total_movimento', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentos_caixas', function (Blueprint $table) {
            $table->dropForeign(['caixa_id']);
            $table->dropForeign(['aluguel_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['metodo_pagamento_id']);
            $table->dropForeign(['cartao_pagamento_id']);
        });

        Schema::dropIfExists('movimentos_caixas');
    }
};
