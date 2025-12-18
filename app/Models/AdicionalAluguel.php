<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdicionalAluguel extends Pivot
{
    protected $table = 'adicionais_alugueis';

    public $incrementing = true;

    protected $fillable = [
        'adicional_id',
        'aluguel_id',
        'quantidade_adicional_aluguel',
        'valor_unitario_adicional_aluguel',
        'valor_total_adicional_aluguel',
        'observacoes_adicional_aluguel',
    ];

    protected $casts = [
        'quantidade_adicional_aluguel' => 'double',
        'valor_unitario_adicional_aluguel' => 'decimal:2',
        'valor_total_adicional_aluguel' => 'decimal:2',
    ];

    /**
     * Um adicional de aluguel pertence a um adicional
     */
    public function adicional(): BelongsTo
    {
        return $this->belongsTo(Adicional::class);
    }

    /**
     * Um adicional de aluguel pertence a um aluguel
     */
    public function aluguel(): BelongsTo
    {
        return $this->belongsTo(Aluguel::class);
    }

    /**
     * Calcula o valor total (quantidade × valor unitário)
     */
    public function getValorTotalAttribute(): float
    {
        return $this->quantidade * $this->valor;
    }

    /**
     * Formata a quantidade
     */
    public function getQuantidadeFormatadaAttribute(): string
    {
        return number_format($this->quantidade, 2, ',', '.');
    }

    /**
     * Formata o valor
     */
    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Formata o valor total
     */
    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }
}
