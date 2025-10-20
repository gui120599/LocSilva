<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carretas', function (Blueprint $table) {
            // Documentação
            $table->string('renavam')->nullable()->after('placa');
            $table->string('chassi')->nullable()->after('renavam');

            // Licenciamento
            $table->date('data_licenciamento')->nullable()->after('chassi');
            $table->date('vencimento_licenciamento')->nullable()->after('data_licenciamento');
            $table->decimal('valor_licenciamento', 10, 2)->nullable()->after('vencimento_licenciamento');
            $table->enum('status_licenciamento', ['regular', 'vencido', 'pendente'])->default('pendente')->after('valor_licenciamento');

            // IPVA
            $table->integer('ano_ipva')->nullable()->after('status_licenciamento');
            $table->date('vencimento_ipva')->nullable()->after('ano_ipva');
            $table->decimal('valor_ipva', 10, 2)->nullable()->after('vencimento_ipva');
            $table->boolean('ipva_pago')->default(false)->after('valor_ipva');
            $table->date('data_pagamento_ipva')->nullable()->after('ipva_pago');

            // Seguros e Manutenção
            $table->date('vencimento_seguro')->nullable()->after('data_pagamento_ipva');
            $table->string('seguradora')->nullable()->after('vencimento_seguro');
            $table->string('numero_apolice')->nullable()->after('seguradora');
            $table->decimal('valor_seguro', 10, 2)->nullable()->after('numero_apolice');

            // Revisões
            $table->date('ultima_revisao')->nullable()->after('valor_seguro');
            $table->date('proxima_revisao')->nullable()->after('ultima_revisao');
            $table->integer('km_atual')->nullable()->after('proxima_revisao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carretas', function (Blueprint $table) {
            $table->dropColumn([
                'renavam',
                'chassi',
                'data_licenciamento',
                'vencimento_licenciamento',
                'valor_licenciamento',
                'status_licenciamento',
                'ano_ipva',
                'vencimento_ipva',
                'valor_ipva',
                'ipva_pago',
                'data_pagamento_ipva',
                'vencimento_seguro',
                'seguradora',
                'numero_apolice',
                'valor_seguro',
                'ultima_revisao',
                'proxima_revisao',
                'km_atual',
            ]);
        });
    }
};
