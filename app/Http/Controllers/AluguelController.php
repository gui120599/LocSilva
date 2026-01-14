<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Carreta;
use App\Models\Cliente;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
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
     * @return view
     * @param $id
     * Imprime o checklist
     */
    public function printChecklist($id): View
    {
        $aluguel = Aluguel::find($id);
        
        return view('checklist',['aluguel' => $aluguel]);
    }

    /**
     * Imprime o recibo de devolução
     */
    public function printDevolucao($id): View
    {
        $aluguel = Aluguel::find($id);

        return view('devolucao',['aluguel' => $aluguel]);
    }


    /**
     * Gera o relatório de aluguéis com base nos filtros
     */
    public function gerarRelatorioAlugueis(Request $request): View
    {
        // Valida os filtros
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'status' => 'nullable|in:ativo,finalizado,cancelado',
            'cliente_id' => 'nullable|exists:clientes,id',
            'carreta_id' => 'nullable|exists:carretas,id',
            'tipo_data' => 'nullable|in:retirada,devolucao_prevista,devolucao_real,criacao',
        ]);

        // Inicia a query
        $query = Aluguel::with([
            'cliente',
            'carreta',
            'movimentos.metodoPagamento'
        ]);

        // Aplica filtro de data
        $tipoData = $request->input('tipo_data', 'retirada');
        $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
        $dataFim = Carbon::parse($request->data_fim)->endOfDay();

        switch ($tipoData) {
            case 'retirada':
                $query->whereBetween('data_retirada', [$dataInicio, $dataFim]);
                break;
            case 'devolucao_prevista':
                $query->whereBetween('data_devolucao_prevista', [$dataInicio, $dataFim]);
                break;
            case 'devolucao_real':
                $query->whereBetween('data_devolucao_real', [$dataInicio, $dataFim]);
                break;
            case 'criacao':
                $query->whereBetween('created_at', [$dataInicio, $dataFim]);
                break;
        }

        // Aplica filtro de status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Aplica filtro de cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Aplica filtro de carreta
        if ($request->filled('carreta_id')) {
            $query->where('carreta_id', $request->carreta_id);
        }

        // Ordena por data de retirada
        $query->orderBy('data_retirada', 'desc');

        // Busca os aluguéis
        $alugueis = $query->get();

        // Monta array de filtros para exibição
        $filtros = [
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'status' => $request->status,
            'tipo_data' => $tipoData,
            'cliente_id' => $request->cliente_id,
            'carreta_id' => $request->carreta_id,
        ];

        // Adiciona nome do cliente se filtrado
        if ($request->filled('cliente_id')) {
            $cliente = Cliente::find($request->cliente_id);
            $filtros['cliente_nome'] = $cliente ? $cliente->nome : null;
        }

        // Adiciona identificação da carreta se filtrado
        if ($request->filled('carreta_id')) {
            $carreta = Carreta::find($request->carreta_id);
            $filtros['carreta_identificacao'] = $carreta ? $carreta->identificacao : null;
        }

        return view('aluguels', compact('alugueis', 'filtros'));
    }

    /**
     * Exporta o relatório em PDF (opcional)
     */
    public function exportarPdfAlugueis(Request $request)
    {
        // Reutiliza a lógica do relatório
        /*$view = $this->gerarRelatorioAlugueis($request);
        
        $pdf = \PDF::loadView('relatorios.listagem-alugueis', $view->getData());
        
        $nomeArquivo = 'relatorio-alugueis-' . now()->format('Y-m-d-His') . '.pdf';
        
        return $pdf->download($nomeArquivo);*/
    }

    /**
     * Gera relatório de aluguéis em Excel (opcional)
     */
    public function exportarExcelAlugueis(Request $request)
    {
        // Implementação com Laravel Excel
        // return Excel::download(new AlugueisExport($request->all()), 'alugueis.xlsx');
    }
}
