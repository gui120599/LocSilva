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
            $table->dateTime('data_retirada')->change();
            $table->dateTime('data_devolucao_prevista')->change();
            $table->dateTime('data_devolucao_real')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alugueis', function (Blueprint $table) {
            $table->date('data_retirada')->change();
            $table->date('data_devolucao_prevista')->change();
            $table->date('data_devolucao_real')->nullable()->change();
        });
    }
};
