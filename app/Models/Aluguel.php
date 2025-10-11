<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aluguel extends Model
{
    protected $table = 'alugueis';

    protected $fillable = [
        'cliente_id',
        'carreta_id',
        'caixa_id',
        'data_retirada',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'valor_diaria',
        'quantidade_diarias',
        'valor_total',
        'valor_pago',
        'valor_saldo',
        'status',
        'observacoes'
    ];

    protected $casts = [
        'data_retirada' => 'date',
        'data_devolucao_prevista' => 'date',
        'data_devolucao_real' => 'date',
        'valor_diaria' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'valor_saldo' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function carreta(): BelongsTo
    {
        return $this->belongsTo(Carreta::class);
    }

    public function caixa(): BelongsTo
    {
        return $this->belongsTo(Caixa::class);
    }
}
