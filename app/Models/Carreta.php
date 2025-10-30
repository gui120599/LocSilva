<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carreta extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'identificacao',
        'foto',
        'documento',
        'tipo',
        'marca',
        'modelo',
        'ano',
        'placa',
        'capacidade_carga',
        'valor_diaria',
        'status',
        'observacoes'
    ];

    protected $casts = [
        'valor_diaria' => 'decimal:2',
        'capacidade_carga' => 'decimal:2',
    ];

/**
     * Uma carreta pode ter vários aluguéis
     */
    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class, 'carreta_id');
    }

    /**
     * Aluguel ativo da carreta
     */
    public function aluguelAtivo()
    { 
        return $this->alugueis()->where('status', 'ativo')->first();
    }

    /**
     * Verifica se está disponível
     */
    public function isDisponivel(): bool
    {
        return $this->status === 'disponivel';
    }

    /**
     * Verifica se está alugada
     */
    public function isAlugada(): bool
    {
        return $this->status === 'alugada';
    }

    /**
     * Marca como alugada
     */
    public function marcarComoAlugada(): void
    {
        $this->update(['status' => 'alugada']);
    }

    /**
     * Marca como disponível
     */
    public function marcarComoDisponivel(): void
    {
        $this->update(['status' => 'disponivel']);
    }

    /**
     * Total arrecadado pela carreta
     */
    public function getTotalArrecadadoAttribute(): float
    {
        return $this->alugueis()
            ->where('status', 'finalizado')
            ->sum('valor_total');
    }
}
