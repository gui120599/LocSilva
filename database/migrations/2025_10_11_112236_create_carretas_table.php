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
        Schema::create('carretas', function (Blueprint $table) {
            $table->id();
            $table->string('identificacao')->unique();
            $table->string('foto')->nullable();
            $table->string('documento')->nullable();
            $table->enum('tipo', ['carreta', 'reboque']);
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('ano')->nullable();
            $table->string('placa')->nullable();
            $table->decimal('capacidade_carga', 8, 2)->nullable();
            $table->decimal('valor_diaria', 8, 2)->default(0);
            $table->decimal('valor_venda', 8, 2)->default(0);
            $table->enum('status', ['disponivel', 'alugada', 'manutencao','baixada','vendida'])->default('disponivel');
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
        Schema::dropIfExists('carretas');
    }
};
