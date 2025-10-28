<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aluguel extends Model
{
    protected $table = 'alugueis';

    use SoftDeletes;
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

// Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function carreta()
    {
        return $this->belongsTo(Carreta::class);
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function movimentos()
    {
        return $this->hasMany(MovimentoCaixa::class);
    }

    // Métodos úteis
    public function finalizar(float $pagamentoAdicional = 0): void
    {
        $this->update([
            'data_devolucao_real' => now(),
            'status' => 'finalizado',
            'valor_pago' => $this->valor_pago + $pagamentoAdicional,
            'valor_saldo' => max(0, $this->valor_total - ($this->valor_pago + $pagamentoAdicional)),
        ]);
    }

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

    public function adicionarPagamento(float $valor, string $descricao = null): void
    {
        $this->valor_pago += $valor;
        $this->valor_saldo = max(0, $this->valor_total - $this->valor_pago);
        $this->save();
    }
}
