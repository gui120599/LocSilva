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

    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class);
    }

    public function isDisponivel(): bool
    {
        return $this->status === 'disponivel';
    }
}
