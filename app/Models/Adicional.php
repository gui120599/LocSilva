<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Adicional extends Model
{
    use SoftDeletes;

    protected $table = 'adicionais';

    protected $fillable = [
        'descricao',
        'foto',
        'valor',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    /**
     * Um adicional pode estar em vários aluguéis (através da pivot)
     */
    public function alugueis(): BelongsToMany
    {
        return $this->belongsToMany(Aluguel::class, 'adicionais_alugueis')
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
     * Calcula o valor total baseado na quantidade
     */
    public function calcularValorTotal(float $quantidade = 1): float
    {
        return $this->valor * $quantidade;
    }

    /**
     * Verifica se tem foto
     */
    public function hasFoto(): bool
    {
        return !empty($this->foto);
    }

    /**
     * Obtém URL da foto
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        return \Storage::disk('public')->url($this->foto);
    }
}
