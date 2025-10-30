<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BandeiraCartaoPagamento extends Model
{
    use SoftDeletes;

    protected $table = 'bandeira_cartao_pagamentos';

    protected $fillable = [
        'bandeira',
        'cnpj_crendeciador',
    ];

    /**
     * Uma bandeira pode ter vários movimentos
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class, 'cartao_pagamento_id');
    }

    /**
     * Obtém o nome formatado da bandeira
     */
    public function getNomeFormatadoAttribute(): string
    {
        return match ($this->bandeira) {
            'None' => 'Nenhuma',
            'AmericanExpress' => 'American Express',
            'DinersClub' => 'Diners Club',
            'BanesCard' => 'BanesCard',
            'CalCard' => 'CalCard',
            'GoodCard' => 'Good Card',
            'GreenCard' => 'Green Card',
            'MaxVan' => 'Max Van',
            'RedeCompras' => 'Rede Compras',
            'ValeCard' => 'Vale Card',
            'Other' => 'Outra',
            default => $this->bandeira,
        };
    }
}
