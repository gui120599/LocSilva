<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'avatar_url',
        'document',
        'phone',
    ];

    /**
     * Determine if the user can access Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url 
        ? Storage::url($this->avatar_url) 
        : null;

    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Um usuário pode ter vários caixas
     */
    public function caixas(): HasMany
    {
        return $this->hasMany(Caixa::class, 'user_id');
    }

    /**
     * Um usuário pode ter vários movimentos
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class, 'user_id');
    }

    /**
     * Caixa aberto do usuário
     */
    public function caixaAberto()
    {
        return $this->caixas()
            ->where('status', 'aberto')
            ->latest()
            ->first();
    }

    /**
     * Verifica se tem caixa aberto
     */
    public function hasCaixaAberto(): bool
    {
        return $this->caixaAberto() !== null;
    }


}
