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
        Schema::create('adicionais', function(Blueprint $table){
            $table->id();
            $table->string('descricao_adicional');
            $table->string('foto_adicional')->nullable();
            $table->decimal('valor_adicional',10,2)->default(0);
            $table->text('observacoes_adicional')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adicionais');
    }
};
