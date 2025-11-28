<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use Illuminate\Http\Request;

class CaixaController extends Controller
{
    public function printCaixa($id)
    {
        $caixa = Caixa::find($id);
        $caixa->load([
            'user',
            'movimentos' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'movimentos.metodoPagamento',
            'movimentos.bandeiraCartao',
            'movimentos.aluguel',
            'movimentos.user'
        ]);

        // Retorna a view de impressão
        return view('caixa', ['caixa' => $caixa]);
    }
}
