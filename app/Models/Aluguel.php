<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluguel extends Model
{
    use SoftDeletes;

    protected $table = 'alugueis';

    protected $fillable = [
        'descricao',
        'cliente_id',
        'carreta_id',
        'data_retirada',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'quantidade_diarias',
        'valor_diaria',
        'valor_acrescimo_aluguel',
        'valor_desconto_aluguel',
        'valor_total_aluguel',
        'valor_pago_aluguel',
        'valor_saldo_aluguel',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_retirada' => 'datetime',
        'data_devolucao_prevista' => 'datetime',
        'data_devolucao_real' => 'datetime',
        'valor_diaria' => 'decimal:2',
        'valor_acrescimo_aluguel' => 'decimal:2',
        'valor_desconto_aluguel' => 'decimal:2',
        'valor_total_aluguel' => 'decimal:2',
        'valor_pago_aluguel' => 'decimal:2',
        'valor_saldo_aluguel' => 'decimal:2',
    ];

    /**
     * Um aluguel pertence a um cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Um aluguel pertence a uma carreta
     */
    public function carreta(): BelongsTo
    {
        return $this->belongsTo(Carreta::class, 'carreta_id');
    }

    /**
     * Um aluguel pode ter vários movimentos de caixa
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class, 'aluguel_id');
    }



    /**
     * Calcula o valor total do aluguel
     */
    public function calcularValorTotal(): float
    {
        $subtotal = $this->valor_diaria * $this->quantidade_diarias;
        return $subtotal + $this->valor_acrescimo - $this->valor_desconto;
    }

    /**
     * Calcula o total já pago
     */
    public function getTotalPagoAttribute(): float
    {
        return $this->movimentos()
            ->where('tipo', 'entrada')
            ->sum('valor_total');
    }

    /**
     * Calcula o saldo restante
     */
    public function getSaldoAttribute(): float
    {
        return max(0, $this->valor_total - $this->total_pago);
    }

    /**
     * Verifica se está pago
     */
    public function isPago(): bool
    {
        return $this->saldo <= 0;
    }

    /**
     * Verifica se está atrasado
     */
    public function isAtrasado(): bool
    {
        if ($this->status !== 'ativo') {
            return false;
        }

        return $this->data_devolucao_prevista->isPast();
    }

    /**
     * Finaliza o aluguel
     */
    public function finalizar(): void
    {
        $this->update([
            'data_devolucao_real' => now(),
            'status' => 'finalizado',
        ]);
    }

    /**
     * Cancela o aluguel
     */
    public function cancelar(string $motivo = null): void
    {
        $observacoes = $this->observacoes ?? '';

        if ($motivo) {
            $observacoes .= "\n\nCancelado em " . now()->format('d/m/Y H:i') . ": {$motivo}";
        }

        $this->update([
            'status' => 'cancelado',
            'observacoes' => $observacoes,
        ]);
    }

    /**
     * Um aluguel pode ter vários adicionais (através da pivot)
     */
    public function adicionais(): BelongsToMany
    {
        return $this->belongsToMany(Adicional::class, 'adicionais_alugueis')
            ->withPivot('quantidade', 'valor', 'observacoes')
            ->withTimestamps()
            ->using(AdicionalAluguel::class);
    }

    /**
     * Relacionamento direto com a tabela pivot
     */
    public function adicionaisAlugueis(): HasMany
    {
        return $this->hasMany(AdicionalAluguel::class);
    }

    /**
     * Calcula o total dos adicionais
     */
    public function getTotalAdicionaisAttribute(): float
    {
        return $this->adicionaisAlugueis->sum(function ($item) {
            return $item->quantidade * $item->valor;
        });
    }

    /**
     * Calcula o valor total do aluguel INCLUINDO adicionais
     */
    public function calcularValorTotalComAdicionais(): float
    {
        $subtotal = $this->valor_diaria * $this->quantidade_diarias;
        $totalAdicionais = $this->total_adicionais;

        return $subtotal
            + ($this->valor_acrescimo_aluguel ?? 0)
            - ($this->valor_desconto_aluguel ?? 0)
            + $totalAdicionais;
    }
}
