<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodoPagamento extends Model
{
    use SoftDeletes;

    protected $table = 'metodos_pagamentos';

    protected $fillable = [
        'nome',
        'taxa_tipo',
        'taxa_percentual',
        'descricao_nfe',
    ];

    protected $casts = [
        'taxa_percentual' => 'decimal:2',
    ];

    /**
     * Um método de pagamento pode ter vários movimentos
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class, 'metodo_pagamento_id');
    }

    /**
     * Calcula o valor com taxa aplicada
     */
    public function calcularValorComTaxa(float $valor): float
    {
        if ($this->taxa_tipo === 'N/A' || $this->taxa_percentual == 0) {
            return $valor;
        }

        $taxa = ($valor * $this->taxa_percentual) / 100;

        return match($this->taxa_tipo) {
            'ACRESCENTAR' => $valor + $taxa,
            'DESCONTAR' => $valor - $taxa,
            default => $valor,
        };
    }

    /**
     * Verifica se é pagamento em dinheiro
     */
    public function isDinheiro(): bool
    {
        return $this->descricao_nfe === 'cash';
    }

    /**
     * Verifica se é cartão
     */
    public function isCartao(): bool
    {
        return in_array($this->descricao_nfe, ['creditCard', 'debitCard']);
    }
}