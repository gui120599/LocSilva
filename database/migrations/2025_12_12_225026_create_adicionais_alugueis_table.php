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
        Schema::create('adicionais_alugueis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adicional_id')->constrained('adicionais');
            $table->foreignId('aluguel_id')->constrained('alugueis');
            $table->double('quantidade_adicional_aluguel')->default(1);
            $table->decimal('valor_unitario_adicional_aluguel',10,2)->default(0);
            $table->decimal('valor_total_adicional_aluguel',10,2)->default(0);
            $table->text('observacoes_adicional_aluguel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adicionais_alugueis', function (Blueprint $table){
            $table->dropForeign(['adicional_id']);
            $table->dropForeign(['aluguel_id']);
        });
        Schema::dropIfExists('adicionais_alugueis');
    }
};
