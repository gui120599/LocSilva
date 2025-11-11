<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Obtém o caminho para o qual o usuário deve ser redirecionado quando não estiver autenticado.
     *
     * Ao usar o Filament, precisamos redirecionar para a rota de login do painel
     * em vez do 'login' padrão do Laravel, que não está definido.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Se a requisição não espera uma resposta JSON (ou seja, é uma requisição web que
        // precisa de redirecionamento), forçamos o redirecionamento para o login do Filament.
        if (! $request->expectsJson()) {
            // Utilizamos o nome da rota do Filament. O padrão é 'filament.admin.auth.login'
            // se o ID do seu Painel (Panel) for 'admin'.
            // Se você nomeou seu Painel com outro ID (ex: 'app'), altere para
            // 'filament.app.auth.login'.
            return route('filament.admin.auth.login');
        }

        return null;
    }
}