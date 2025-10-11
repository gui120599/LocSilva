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
        Schema::create('movimento_caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas');
            $table->foreignId('aluguel_id')->nullable()->constrained('alugueis');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->decimal('valor', 10, 2);
            $table->string('descricao');
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
