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
        Schema::create('alugueis', function (Blueprint $table) {
            $table->id();
            $table->string('descricao')->nullable();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('carreta_id')->constrained('carretas');
            $table->date('data_retirada');
            $table->date('data_devolucao_prevista');
            $table->date('data_devolucao_real')->nullable();
            $table->integer('quantidade_diarias');
            $table->decimal('valor_diaria', 10, 2)->default(0);
            $table->decimal('valor_acrescimo_aluguel', 10, 2)->default(0);
            $table->decimal('valor_desconto_aluguel', 10, 2)->default(0);
            $table->decimal('valor_total_aluguel', 10, 2)->default(0);
            $table->decimal('valor_pago_aluguel', 10, 2)->default(0);
            $table->decimal('valor_saldo_aluguel', 10, 2)->default(0);
            $table->enum('status', ['ativo', 'pendente', 'finalizado', 'cancelado'])->default('ativo');
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alugueis',function (Blueprint $table){
            $table->dropForeign(['carreta_id']);
            $table->dropForeign(['cliente_id']);
        });

        Schema::dropIfExists('alugueis');
    }
};
