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
        Schema::create('arquivos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('tipo_documento_id')->constrained('tipos_documentos');
            $table->string('url_documento');
            $table->date('data_validade_documento')->nullable();
            $table->enum('status_documento', ['PENDENTE','ATIVO', 'VENCIDO', 'EXPIRANDO'])->default('PENDENTE');
            $table->string('observacoes_documento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arquivos_clientes');
    }
};
