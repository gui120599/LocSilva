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
    <div class="p-1 max-w-6xl mx-auto ring-1 ring-gray-100/70">
        <!-- Header Principal e Logo -->
        <header
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-2 mb-2">
            <div class="space-y-1">
                <h2 class="font-extrabold text-gray-800">
                    Fechamento de Caixa <span class="text-primary-600">#{{ $caixa->id }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                    Operador: {{ $caixa->user->name ?? 'Não informado' }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Seção de Datas e Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-2">

            <!-- Card: Abertura -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-calendar class="w-5 h-5 mr-2 text-green-500" /> Abertura
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y') }}</p>
                    <p><strong>Horário:</strong> {{ \Carbon\Carbon::parse($caixa->data_abertura)->format('H:i:s') }}</p>
                </div>
            </div>

            <!-- Card: Fechamento -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-calendar class="w-5 h-5 mr-2 text-red-500" /> Fechamento
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Data:</strong>
                        {{ $caixa->data_fechamento ? \Carbon\Carbon::parse($caixa->data_fechamento)->format('d/m/Y') :
    'Em aberto' }}
                    </p>
                    <p><strong>Horário:</strong>
                        {{ $caixa->data_fechamento ? \Carbon\Carbon::parse($caixa->data_fechamento)->format('H:i:s') :
    '--:--:--' }}
                    </p>
                </div>
            </div>

            <!-- Card: Status -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-information-circle class="w-5 h-5 mr-2 text-blue-500" /> Status
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Situação:</strong>
                        <span
                            class="px-2 py-1 rounded-md text-xs font-semibold {{ $caixa->status === 'aberto' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($caixa->status) }}
                        </span>
                    </p>
                    <p><strong>Movimentos:</strong> {{ $caixa->movimentos->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Resumo Financeiro (Destaque) -->
        <div class="p-2 border border-gray-200 bg-primary-50/30 rounded-xl mb-3">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center">
                <x-heroicon-s-currency-dollar class="w-6 h-6 mr-2 text-primary-600" /> Resumo Financeiro
            </h5>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <!-- Total Entradas -->
                <div class="text-center p-2 bg-white rounded-lg border border-green-200">
                    <p class="text-sm text-gray-600 mb-1">Total Entradas</p>
                    <p class="text-xl font-bold text-green-600">
                        R$ {{ number_format($caixa->total_entradas, 2, ',', '.') }}
                    </p>
                </div>

                <!-- Total Saídas -->
                <div class="text-center p-3 bg-white rounded-lg border border-red-200">
                    <p class="text-sm text-gray-600 mb-1">Total Saídas</p>
                    <p class="text-xl font-bold text-red-600">
                        R$ {{ number_format($caixa->total_saidas, 2, ',', '.') }}
                    </p>
                </div>

                <!-- Saldo Final -->
                <div class="text-center p-3 bg-white rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-600 mb-1">Saldo Final</p>
                    <p class="text-xl font-bold text-blue-600">
                        R$ {{ number_format($caixa->saldo_atual, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Resumo por Método de Pagamento -->
        <div class="grid grid-cols-1 gap-3 mb-3">
            <div class="p-3 border border-gray-200 bg-white rounded-xl">
                <h5 class="font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-credit-card class="w-5 h-5 mr-2 text-primary-500" /> Resumo por Método de Pagamento
                </h5>

                @php
                    // Agrupa movimentos por método de pagamento
                    $resumoPorMetodo = $caixa->movimentos->groupBy('metodo_pagamento_id')->map(function ($movimentos) {
                        return [
                            'metodo' => $movimentos->first()->metodoPagamento->nome ?? 'Não especificado',
                            'entradas' => $movimentos->where('tipo', 'entrada')->sum('valor_total_movimento'),
                            'saidas' => $movimentos->where('tipo', 'saida')->sum('valor_total_movimento'),
                            'total' => $movimentos->where('tipo', 'entrada')->sum('valor_total_movimento') - $movimentos->where('tipo', 'saida')->sum('valor_total_movimento'),
                        ];
                    });
                @endphp

                @if($resumoPorMetodo->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-[8px]">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left p-1 font-semibold text-gray-700">Método</th>
                                    <th class="text-right p-1 font-semibold text-green-700">Entradas</th>
                                    <th class="text-right p-1 font-semibold text-red-700">Saídas</th>
                                    <th class="text-right p-1 font-semibold text-blue-700">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumoPorMetodo as $resumo)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-1 text-gray-700">{{ $resumo['metodo'] }}</td>
                                        <td class="p-1 text-right text-green-700">
                                            R$ {{ number_format($resumo['entradas'], 2, ',', '.') }}
                                        </td>
                                        <td class="p-1 text-right text-red-700">
                                            R$ {{ number_format($resumo['saidas'], 2, ',', '.') }}
                                        </td>
                                        <td class="p-1 text-right font-semibold text-blue-700">
                                            R$ {{ number_format($resumo['total'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic text-center py-4">Nenhum movimento registrado</p>
                @endif
            </div>
        </div>

        <!-- Histórico Detalhado de Movimentos -->
        <div class="grid grid-cols-1 gap-3">
            <div class="p-2 border border-gray-200 bg-white rounded-xl">
                <h5 class="font-bold text-gray-700 mb-2 flex items-center">
                    <x-heroicon-s-clipboard-document-list class="w-5 h-5 mr-2 text-primary-500" /> Movimentos Detalhados
                </h5>

                @if($caixa->movimentos->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-[8px]">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left p-1 font-semibold text-gray-700">Data/Hora</th>
                                    <th class="text-left p-1 font-semibold text-gray-700">Tipo</th>
                                    <th class="text-left p-1 font-semibold text-gray-700">Descrição</th>
                                    <th class="text-left p-1 font-semibold text-gray-700">Método</th>
                                    <th class="text-right p-1 font-semibold text-gray-700">Valor Pago</th>
                                    <th class="text-right p-1 font-semibold text-gray-700">Valor Receb.</th>
                                    <th class="text-right p-1 font-semibold text-gray-700">Troco</th>
                                    <th class="text-right p-1 font-semibold text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($caixa->movimentos->sortBy('created_at') as $movimento)
                                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                                        <td class="p-1 text-gray-600">
                                                            {{ $movimento->created_at->format('d/m/Y H:i') }}
                                                        </td>
                                                        <td class="p-1">
                                                            <span
                                                                class="px-2 py-0.5 rounded font-semibold {{ $movimento->tipo === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                {{ ucfirst($movimento->tipo) }}
                                                            </span>
                                                        </td>
                                                        <td class="p-1 text-gray-700">
                                                            {{ Str::limit($movimento->descricao ?? 'Sem descrição', 30) }}
                                                            @if($movimento->aluguel_id)
                                                                <span class="text-blue-600">(Aluguel #{{ $movimento->aluguel_id }})</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-1 text-gray-600">
                                                            {{ $movimento->metodoPagamento->nome ?? '-' }}
                                                            @if($movimento->bandeiraCartao)
                                                                <span class="text-xs text-gray-500">({{ $movimento->bandeiraCartao->nome }})</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-1 text-right text-gray-700">
                                                            R$ {{ number_format($movimento->valor_pago_movimento ?? 0, 2, ',', '.') }}
                                                        </td>
                                                        <td class="p-1 text-right text-green-600">
                                                            {{ $movimento->valor_recebido_movimento > 0 ? 'R$ ' .
                                    number_format($movimento->valor_recebido_movimento, 2, ',', '.') : '-' }}
                                                        </td>
                                                        <td class="p-1 text-right text-orange-600">
                                                            {{ $movimento->troco_movimento > 0 ? 'R$ ' .
                                    number_format($movimento->troco_movimento, 2, ',', '.') : '-' }}
                                                        </td>
                                                        <td
                                                            class="p-1 text-right font-semibold {{ $movimento->tipo === 'entrada' ? 'text-green-700' : 'text-red-700' }}">
                                                            R$ {{ number_format($movimento->valor_total_movimento, 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic text-center py-4">Nenhum movimento registrado neste caixa</p>
                @endif
            </div>
        </div>

        <!-- Observações -->
        @if($caixa->observacoes)
            <div class="p-3 border border-gray-200 bg-gray-50 rounded-xl mb-3">
                <h3 class="text-lg font-bold text-gray-700 mb-2 flex items-center">
                    <x-heroicon-s-chat-bubble-left-right class="w-5 h-5 mr-2 text-primary-500" /> Observações
                </h3>
                <p class="text-sm text-gray-600">{{ $caixa->observacoes }}</p>
            </div>
        @endif

        <!-- Seção de Assinaturas -->
        <div class="grid grid-cols-2 gap-12 text-center mt-4">
            <!-- Operador -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                <p class="font-semibold text-gray-800">{{ $caixa->user->name ?? 'Não informado' }}</p>
                <p class="text-sm text-gray-500">Operador de Caixa</p>
            </div>

            <!-- Responsável/Gerente -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                <p class="font-semibold text-gray-800">22.341.672 IVAN DE AQUINO SILVA - ME</p>
                <p class="text-sm text-gray-500">Responsável (22.341.672/0001-01)</p>
            </div>
        </div>

        <footer class="mt-6 pt-4 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">
                Relatório gerado em {{ now()->format('d/m/Y \à\s H:i:s') }} | Este é um documento interno
            </p>
        </footer>
    </div>

    <script>
        window.print();
    </script>
</body>