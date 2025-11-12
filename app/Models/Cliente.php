<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nome',
        'data_nascimento',
        'foto',
        'cpf_cnpj',
        'telefone',
        'email',
        'endereco',
        'complemento_endereco',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'observacoes',
    ];


    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class, 'cliente_id');
    }

    /**
     * Aluguéis ativos do cliente
     */
    public function alugueisAtivos(): HasMany
    {
        return $this->alugueis()->where('status', 'ativo');
    }

    /**
     * Total gasto pelo cliente
     */
    public function getTotalGastoAttribute(): float
    {
        return $this->alugueis()
            ->whereIn('status', ['finalizado', 'ativo'])
            ->sum('valor_total');
    }

    /**
     * Total em aberto do cliente
     */
    public function getTotalAbertoAttribute(): float
    {
        return $this->alugueis()
            ->where('status', 'ativo')
            ->get()
            ->sum('saldo');
    }
}
