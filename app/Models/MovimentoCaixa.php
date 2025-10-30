<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentoCaixa extends Model
{
    use SoftDeletes;

    protected $table = 'movimentos_caixas';

    protected $fillable = [
        'caixa_id',
        'aluguel_id',
        'user_id',
        'descricao',
        'tipo',
        'metodo_pagamento_id',
        'cartao_pagamento_id',
        'autorizacao',
        'valor_pago',
        'valor_recebido',
        'valor_acrescimo',
        'valor_desconto',
        'troco',
        'valor_total',
        'data_movimento',
    ];

    protected $casts = [
        'valor_pago' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_acrescimo' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'troco' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'data_movimento' => 'datetime',
    ];

    /**
     * Um movimento pertence a um caixa
     */
    public function caixa(): BelongsTo
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }

    /**
     * Um movimento pode pertencer a um aluguel
     */
    public function aluguel(): BelongsTo
    {
        return $this->belongsTo(Aluguel::class, 'aluguel_id');
    }

    /**
     * Um movimento pertence a um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Um movimento pertence a um método de pagamento
     */
    public function metodoPagamento(): BelongsTo
    {
        return $this->belongsTo(MetodoPagamento::class, 'metodo_pagamento_id');
    }

    /**
     * Um movimento pode pertencer a uma bandeira de cartão
     */
    public function bandeiraCartao(): BelongsTo
    {
        return $this->belongsTo(BandeiraCartaoPagamento::class, 'cartao_pagamento_id');
    }

    /**
     * Verifica se é entrada
     */
    public function isEntrada(): bool
    {
        return $this->tipo === 'entrada';
    }

    /**
     * Verifica se é saída
     */
    public function isSaida(): bool
    {
        return $this->tipo === 'saida';
    }

    /**
     * Verifica se tem troco
     */
    public function hasTroco(): bool
    {
        return $this->troco > 0;
    }

    /**
     * Verifica se foi pago com cartão
     */
    public function isPagamentoCartao(): bool
    {
        return $this->cartao_pagamento_id !== null;
    }

    /**
     * Calcula o valor total do movimento
     */
    public function calcularValorTotal(): float
    {
        return $this->valor_pago + $this->valor_acrescimo - $this->valor_desconto;
    }
}