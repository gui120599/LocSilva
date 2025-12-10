<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else

    @endif
</head>

<body>
    <div class="p-1 max-w-7xl mx-auto ring-1 ring-gray-100/70">
        <!-- Header Principal e Logo -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-primary-600 pb-2 mb-2">
            <div class="space-y-1">
                <h2 class="text-lg font-extrabold text-gray-800">
                    RELATÓRIO DE ALUGUÉIS
                </h2>
                <p class="text-xs text-gray-500">
                    Período: {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} 
                    até {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}
                </p>
                <p class="text-xs text-gray-500">
                    Emitido em: {{ now()->format('d/m/Y \à\s H:i:s') }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Informações da Empresa -->
        <div class="p-2 mb-2 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[10px] text-gray-700">
                <div>
                    <p><strong>Empresa:</strong> 22.341.672 IVAN DE AQUINO SILVA - ME</p>
                    <p><strong>CNPJ:</strong> 22.341.672/0001-01</p>
                </div>
                <div>
                    <p><strong>Telefone:</strong> (62) 9 9323-9697</p>
                    <p><strong>Endereço:</strong> R. Maria Conceição, 245, Parque Amazonia, Goiânia - GO</p>
                </div>
            </div>
        </div>

        <!-- Filtros Aplicados -->
        <div class="p-2 mb-2 border border-blue-200 bg-blue-50 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-funnel class="w-5 h-5 mr-2 text-blue-600" /> FILTROS APLICADOS
            </h5>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[9px] text-gray-700">
                @if(isset($filtros['status']) && $filtros['status'])
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-gray-600">Status:</p>
                        <p class="font-bold capitalize">{{ $filtros['status'] }}</p>
                    </div>
                @endif

                @if(isset($filtros['cliente_id']) && $filtros['cliente_id'])
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-gray-600">Cliente:</p>
                        <p class="font-bold">{{ $filtros['cliente_nome'] ?? 'Selecionado' }}</p>
                    </div>
                @endif

                @if(isset($filtros['carreta_id']) && $filtros['carreta_id'])
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-gray-600">Carreta:</p>
                        <p class="font-bold">{{ $filtros['carreta_identificacao'] ?? 'Selecionada' }}</p>
                    </div>
                @endif

                @if(isset($filtros['tipo_data']) && $filtros['tipo_data'])
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-gray-600">Tipo de Data:</p>
                        <p class="font-bold capitalize">{{ $filtros['tipo_data'] }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Resumo Geral -->
        <div class="p-2 mb-2 border-2 border-green-300 bg-green-50 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-chart-bar class="w-5 h-5 mr-2 text-green-600" /> RESUMO GERAL
            </h5>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center">
                <div class="p-2 bg-white rounded-lg border border-gray-200">
                    <p class="text-[9px] text-gray-600 mb-1">Total de Aluguéis</p>
                    <p class="text-xl font-bold text-blue-700">{{ $alugueis->count() }}</p>
                </div>
                
                <div class="p-2 bg-white rounded-lg border border-gray-200">
                    <p class="text-[9px] text-gray-600 mb-1">Valor Total</p>
                    <p class="text-lg font-bold text-green-600">
                        R$ {{ number_format($alugueis->sum('valor_total_aluguel'), 2, ',', '.') }}
                    </p>
                </div>
                
                <div class="p-2 bg-white rounded-lg border border-gray-200">
                    <p class="text-[9px] text-gray-600 mb-1">Total Pago</p>
                    <p class="text-lg font-bold text-green-700">
                        R$ {{ number_format($alugueis->sum(function($a) { return $a->movimentos->sum('valor_total_movimento'); }), 2, ',', '.') }}
                    </p>
                </div>
                
                <div class="p-2 bg-white rounded-lg border border-gray-200">
                    <p class="text-[9px] text-gray-600 mb-1">Saldo Pendente</p>
                    <p class="text-lg font-bold text-orange-600">
                        R$ {{ number_format($alugueis->sum('valor_saldo_aluguel'), 2, ',', '.') }}
                    </p>
                </div>
                
                <div class="p-2 bg-white rounded-lg border border-gray-200">
                    <p class="text-[9px] text-gray-600 mb-1">Diárias Total</p>
                    <p class="text-xl font-bold text-purple-700">{{ $alugueis->sum('quantidade_diarias') }}</p>
                </div>
            </div>
        </div>

        <!-- Tabela de Aluguéis -->
        <div class="p-2 mb-2 border border-gray-200 bg-white rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-clipboard-document-list class="w-5 h-5 mr-2 text-primary-600" /> LISTAGEM DETALHADA
            </h5>

            @if($alugueis->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-[8px]">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="text-left p-1 font-bold text-gray-700">#</th>
                                <th class="text-left p-1 font-bold text-gray-700">Cliente</th>
                                <th class="text-left p-1 font-bold text-gray-700">Carreta</th>
                                <th class="text-left p-1 font-bold text-gray-700">Retirada</th>
                                <th class="text-left p-1 font-bold text-gray-700">Devolução Prev.</th>
                                <th class="text-left p-1 font-bold text-gray-700">Devolução Real</th>
                                <th class="text-center p-1 font-bold text-gray-700">Diárias</th>
                                <th class="text-right p-1 font-bold text-gray-700">Valor Total</th>
                                <th class="text-right p-1 font-bold text-gray-700">Pago</th>
                                <th class="text-right p-1 font-bold text-gray-700">Saldo</th>
                                <th class="text-center p-1 font-bold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alugueis as $aluguel)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-1 text-gray-700 font-semibold">{{ $aluguel->id }}</td>
                                    <td class="p-1 text-gray-700">
                                        {{ Str::limit($aluguel->cliente->nome, 20) }}
                                        <br>
                                        <span class="text-[7px] text-gray-500">{{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }}</span>
                                    </td>
                                    <td class="p-1 text-gray-700">
                                        {{ $aluguel->carreta->identificacao }}
                                        <br>
                                        <span class="text-[7px] text-gray-500 font-mono">{{ $aluguel->carreta->placa }}</span>
                                    </td>
                                    <td class="p-1 text-gray-700">
                                        {{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('d/m/Y') }}
                                        <br>
                                        <span class="text-[7px] text-gray-500">{{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('H:i') }}</span>
                                    </td>
                                    <td class="p-1 text-gray-700">
                                        {{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('d/m/Y') }}
                                        <br>
                                        <span class="text-[7px] text-gray-500">{{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('H:i') }}</span>
                                    </td>
                                    <td class="p-1 text-gray-700">
                                        @if($aluguel->data_devolucao_real)
                                            {{ \Carbon\Carbon::parse($aluguel->data_devolucao_real)->format('d/m/Y') }}
                                            <br>
                                            <span class="text-[7px] text-gray-500">{{ \Carbon\Carbon::parse($aluguel->data_devolucao_real)->format('H:i') }}</span>
                                        @else
                                            <span class="text-[7px] text-orange-600 font-semibold">Em andamento</span>
                                        @endif
                                    </td>
                                    <td class="p-1 text-center font-bold text-blue-700">{{ $aluguel->quantidade_diarias }}</td>
                                    <td class="p-1 text-right font-semibold text-gray-800">
                                        R$ {{ number_format($aluguel->valor_total_aluguel, 2, ',', '.') }}
                                    </td>
                                    <td class="p-1 text-right font-semibold text-green-700">
                                        R$ {{ number_format($aluguel->movimentos->sum('valor_total_movimento'), 2, ',', '.') }}
                                    </td>
                                    <td class="p-1 text-right font-semibold text-orange-600">
                                        R$ {{ number_format($aluguel->valor_saldo_aluguel ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="p-1 text-center">
                                        <span class="px-2 py-0.5 rounded text-[7px] font-bold
                                            @if($aluguel->status == 'ativo') bg-green-100 text-green-800
                                            @elseif($aluguel->status == 'finalizado') bg-blue-100 text-blue-800
                                            @elseif($aluguel->status == 'cancelado') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($aluguel->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 border-t-2 border-gray-400">
                            <tr class="font-bold">
                                <td colspan="6" class="p-1 text-right text-gray-800">TOTAIS:</td>
                                <td class="p-1 text-center text-blue-700">{{ $alugueis->sum('quantidade_diarias') }}</td>
                                <td class="p-1 text-right text-gray-800">
                                    R$ {{ number_format($alugueis->sum('valor_total_aluguel'), 2, ',', '.') }}
                                </td>
                                <td class="p-1 text-right text-green-700">
                                    R$ {{ number_format($alugueis->sum(function($a) { return $a->movimentos->sum('valor_total_movimento'); }), 2, ',', '.') }}
                                </td>
                                <td class="p-1 text-right text-orange-600">
                                    R$ {{ number_format($alugueis->sum('valor_saldo_aluguel'), 2, ',', '.') }}
                                </td>
                                <td class="p-1"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500 italic py-8">Nenhum aluguel encontrado com os filtros aplicados</p>
            @endif
        </div>

        <!-- Estatísticas por Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2">
            @php
                $porStatus = $alugueis->groupBy('status');
            @endphp
            
            @foreach(['ativo' => 'green', 'finalizado' => 'blue', 'cancelado' => 'red'] as $status => $cor)
                @php
                    $statusAlugueis = $porStatus->get($status, collect());
                @endphp
                <div class="p-2 border border-gray-200 bg-white rounded-lg">
                    <h5 class="font-bold text-gray-800 mb-2 text-[10px] capitalize">
                        {{ $status }}
                    </h5>
                    <div class="space-y-1 text-[9px]">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Quantidade:</span>
                            <span class="font-bold text-{{ $cor }}-700">{{ $statusAlugueis->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Valor Total:</span>
                            <span class="font-bold text-{{ $cor }}-700">
                                R$ {{ number_format($statusAlugueis->sum('valor_total_aluguel'), 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Diárias:</span>
                            <span class="font-bold text-{{ $cor }}-700">{{ $statusAlugueis->sum('quantidade_diarias') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Carretas Mais Alugadas -->
        
        @php
            $carretasMaisAlugadas = $alugueis->groupBy('carreta_id')->map(function($group) {
                return [
                    'carreta' => $group->first()->carreta,
                    'quantidade' => $group->count(),
                    'total' => $group->sum('valor_total_aluguel'),
                    'diarias' => $group->sum('quantidade_diarias')
                ];
            })->sortByDesc('quantidade')->take(5);
        @endphp

        @if($carretasMaisAlugadas->count())
        <!--<div class="p-2 mb-2 border border-gray-200 bg-white rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-trophy class="w-5 h-5 mr-2 text-yellow-600" /> TOP 5 CARRETAS MAIS ALUGADAS
            </h5>
            <div class="overflow-x-auto">
                <table class="w-full text-[8px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-1 font-bold text-gray-700">Posição</th>
                            <th class="text-left p-1 font-bold text-gray-700">Identificação</th>
                            <th class="text-left p-1 font-bold text-gray-700">Placa</th>
                            <th class="text-center p-1 font-bold text-gray-700">Aluguéis</th>
                            <th class="text-center p-1 font-bold text-gray-700">Diárias</th>
                            <th class="text-right p-1 font-bold text-gray-700">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carretasMaisAlugadas as $index => $item)
                            <tr class="border-b border-gray-100">
                                <td class="p-1">
                                    <span class="font-bold text-yellow-600">{{ $index + 1 }}º</span>
                                </td>
                                <td class="p-1 text-gray-700">{{ $item['carreta']->identificacao }}</td>
                                <td class="p-1 text-gray-700 font-mono">{{ $item['carreta']->placa }}</td>
                                <td class="p-1 text-center font-bold text-blue-700">{{ $item['quantidade'] }}</td>
                                <td class="p-1 text-center font-bold text-purple-700">{{ $item['diarias'] }}</td>
                                <td class="p-1 text-right font-semibold text-green-700">
                                    R$ {{ number_format($item['total'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>-->
        @endif

        <footer class="pt-3 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                Este relatório é apenas para fins gerenciais e não tem valor fiscal
            </p>
            <p class="text-xs text-gray-400">
                Relatório gerado em {{ now()->format('d/m/Y \à\s H:i:s') }} por {{ auth()->user()->name ?? 'Sistema' }}
            </p>
        </footer>

    </div>
    
    <script>
        window.print();
    </script>
</body>