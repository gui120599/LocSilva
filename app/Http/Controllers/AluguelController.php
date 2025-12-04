<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AluguelController extends Controller
{
    /**
     * @return void
     * @params $id
     */
    public function printAluguel($id)
    {
        $aluguel = Aluguel::find($id);

        /*$pdf = \PDF::loadView('pdf.aluguel', ['aluguel' => $aluguel]);
        return $pdf->stream();*/

        return view('aluguel', ['aluguel' => $aluguel]);
    }

    /**
     * @return view
     * @params $id
     * Imprime o recibo de retirada
     */
    public function printRetirada($id): View
    {
        $aluguel = Aluguel::find($id);

        return view('retirada',['aluguel' => $aluguel]);
    }

    /**
     * Imprime o recibo de devolução
     */
    public function printDevolucao($id): View
    {
        $aluguel = Aluguel::find($id);

        return view('devolucao',['aluguel' => $aluguel]);
    }
}
