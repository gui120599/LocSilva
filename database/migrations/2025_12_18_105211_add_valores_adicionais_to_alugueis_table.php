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
        Schema::table('alugueis', function (Blueprint $table) {
            $table->decimal('valor_diaria_adicionais', 10, 2)->default(0)->after('valor_diaria');
            $table->decimal('valor_adicionais_aluguel', 10, 2)->default(0)->after('valor_diaria_adicionais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alugueis', function (Blueprint $table) {
            $table->dropColumn(['valor_diaria_adicionais', 'valor_adicionais_aluguel']);
        });
    }
};
