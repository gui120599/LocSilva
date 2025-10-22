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

    public function alugueis(): HasMany
    {
        return $this->hasMany(Aluguel::class);
    }

    public function isDisponivel(): bool
    {
        return $this->status === 'disponivel';
    }

    public static function getCleanOptionsString($carreta)
    {
        return view('filament.components.selectUserResults',[
            'identificacao' => $carreta['identificacao'] ?? null,
            'foto' => $carreta['foto'] ?? null,
            'valor_diaria' => $carreta['valor_diaria'] ?? null,
        ])->render();
    }

    public static function disponiveisParaSelect(): array
    {
        return static::where('status', 'disponivel')
            ->get()
            ->mapWithKeys(fn ($c) => [
                $c->id => "{$c->identificacao} - {$c->valor_diaria}/{$c->placa}",
            ])
            ->toArray();
    }
}
