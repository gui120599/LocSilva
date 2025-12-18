<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('carretas')->insert([
            [
                'identificacao' => 'Carreta 1',
                'foto' => 'fotos_carretas/01K9DBJ80E5MKQTRM3S0XY4EC1.jpg',
                'tipo' => 'carreta',
                'marca' => 'Marca A',
                'modelo' => 'Modelo X',
                'ano' => 2020,
                'placa' => 'ABC-1234',
                'capacidade_carga' => 15000,
                'valor_diaria' => 300,
                'valor_venda' => 80000,
                'status' => 'disponivel',
            ],
            [
                'identificacao' => 'Reboque 1',
                'foto' => 'fotos_carretas/01K9DBK06S4VYV7CGHWN06HH80.jpg',
                'tipo' => 'reboque',
                'marca' => 'Marca B',
                'modelo' => 'Modelo Y',
                'ano' => 2019,
                'placa' => 'DEF-5678',
                'capacidade_carga' => 10000,
                'valor_diaria' => 200,
                'valor_venda' => 50000,
                'status' => 'disponivel',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carretas');
    }
};
