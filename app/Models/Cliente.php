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
        'bairro',
        'cidade',
        'estado',
        'cep',
        'observacoes',
    ];


    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class);
    }
}
