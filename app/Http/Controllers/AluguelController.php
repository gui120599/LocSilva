<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;

class AluguelController extends Controller
{
    /**
     * @return void
     * @params $id
     */
    public function printAluguel($id)
    {
        $aluguel = Aluguel::find($id);

        $pdf = \PDF::loadView('pdf.aluguel', ['aluguel' => $aluguel]);
        return $pdf->stream();
    }
}
