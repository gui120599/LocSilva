<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Caixa extends Model
{
    protected $fillable = [
        'user_id',
        'data_abertura',
        'data_fechamento',
        'saldo_inicial',
        'total_entradas',
        'total_saidas',
        'saldo_final',
        'status',
        'observacoes'
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_fechamento' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'total_entradas' => 'decimal:2',
        'total_saidas' => 'decimal:2',
        'saldo_final' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class);
    }

    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class);
    }
}
