<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caixa extends Model
{
    use SoftDeletes;

    protected $table = 'caixas';

    protected $fillable = [
        'user_id',
        'data_abertura',
        'data_fechamento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_fechamento' => 'datetime',
    ];

    /**
     * Um caixa pertence a um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Um caixa pode ter vários movimentos
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class, 'caixa_id');
    }

    /**
     * Calcula o total de entradas
     */
    public function getTotalEntradasAttribute(): float
    {
        return $this->movimentos()
            ->where('tipo', 'entrada')
            ->sum('valor_total');
    }

    /**
     * Calcula o total de saídas
     */
    public function getTotalSaidasAttribute(): float
    {
        return $this->movimentos()
            ->where('tipo', 'saida')
            ->sum('valor_total');
    }

    /**
     * Calcula o saldo atual do caixa
     */
    public function getSaldoAtualAttribute(): float
    {
        return $this->total_entradas - $this->total_saidas;
    }

    /**
     * Verifica se o caixa está aberto
     */
    public function isAberto(): bool
    {
        return $this->status === 'aberto';
    }

    /**
     * Fecha o caixa
     */
    public function fechar(): void
    {
        $this->update([
            'data_fechamento' => now(),
            'status' => 'fechado',
        ]);
    }

    /**
     * Reabre o caixa
     */
    public function reabrir(): void
    {
        $this->update([
            'data_fechamento' => null,
            'status' => 'aberto',
        ]);
    }
}