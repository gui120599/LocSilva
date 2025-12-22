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
                    Contrato de Aluguel <span class="text-primary-600">#{{ $aluguel->id }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                    Emitido em: {{ \Carbon\Carbon::parse($aluguel->created_at)->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Seção de Detalhes (Grid 3 Colunas) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-2">

            <!-- Card 1: Locatário (Cliente) -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-user class="w-5 h-5 mr-2 text-primary-500" /> Locatário
                </h3>
                <div class="space-y-1 text-[9px] text-gray-600">
                    <p><strong>Nome: </strong> {{ $aluguel->cliente->nome }}</p>
                    <p><strong>CPF/CNPJ: </strong> {{ $aluguel->cliente->cpf_cnpj }}</p>
                    <p><strong>Telefone: </strong> {{ $aluguel->cliente->telefone }}</p>
                    <p><strong>Endereço: </strong> {{ $aluguel->cliente->endereco ?? 'Não informado' }}</p>
                </div>
            </div>

            <!-- Card 2: Locador (Sua Empresa) -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-building-office-2 class="w-5 h-5 mr-2 text-primary-500" /> Locador
                </h3>
                <div class="space-y-1 text-[9px] text-gray-600">
                    <p><strong>Empresa: </strong> 22.341.672 IVAN DE AQUINO SILVA - ME</p>
                    <p><strong>CNPJ: </strong> 22.341.672/0001-01</p>
                    <p><strong>Telefone: </strong> (62) 9 9323-9697</p>
                    <p><strong>Endereço: </strong> R. Maria Conceição, 245, Parque Amazonia, Goiânia - GO, 74840-750</p>
                </div>
            </div>

            <!-- Card 3: Carreta/Reboque -->
            <div class="p-3 border border-gray-100 bg-gray-50/50 rounded-xl transition duration-300">
                <h3 class="text-lg font-bold text-gray-700 mb-1 flex items-center">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-500" /> Reboque
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong class="text-[9px]">Identificação: </strong> {{ $aluguel->carreta->identificacao }}</p>
                    <p><strong class="text-[9px]">Placa: </strong><span
                            class="font-mono text-base bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">{{ $aluguel->carreta->placa }}</span>
                    </p>
                    <p><strong class="text-[9px]">Valor Diária: </strong> <span class="font-semibold text-green-600">R$
                            {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</span></p>
                </div>
            </div>
        </div>

        <!-- Seção Check-in e Check-out -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">

            <!-- Check-in -->
            <div class="p-3 border border-gray-200 bg-white rounded-xl">
                <h3 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-arrow-uturn-right class="w-5 h-5 mr-2 text-blue-500" /> Check-in
                </h3>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between font-medium text-gray-700">
                        <dt>Data:</dt>
                        <dd>{{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div class="flex justify-between text-gray-700">
                        <dt>Cliente:</dt>
                        <dd>{{ $aluguel->cliente->nome }}</dd>
                    </div>

                    <div class="flex justify-between text-gray-700">
                        <dt>Carreta:</dt>
                        <dd>{{ $aluguel->carreta->nome ?? $aluguel->carreta->descricao }}</dd>
                    </div>

                    <div class="flex justify-between text-gray-700">
                        <dt>Observações:</dt>
                        <dd class="text-right max-w-[65%]">
                            {{ $aluguel->observacoes ? Str::limit($aluguel->observacoes, 80) : 'Nenhuma' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Check-out -->
            <div class="p-3 border border-gray-200 bg-white rounded-xl">
                <h3 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-arrow-uturn-left class="w-5 h-5 mr-2 text-red-500" /> Check-out
                </h3>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between font-medium text-gray-700">
                        <dt>Data Prevista:</dt>
                        <dd>{{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div class="flex justify-between font-medium text-gray-700">
                        <dt>Data Real:</dt>
                        <dd>
                            {{ $aluguel->data_devolucao_real
                                ? \Carbon\Carbon::parse($aluguel->data_devolucao_real)->format('d/m/Y H:i')
                                : 'Ainda não devolvido' }}
                        </dd>
                    </div>

                    <div class="flex justify-between text-gray-700">
                        <dt>Diárias calculadas:</dt>
                        <dd>{{ $aluguel->quantidade_diarias }}</dd>
                    </div>

                    <div class="flex justify-between text-gray-700">
                        <dt>Tolerância:</dt>
                        <dd>20 minutos</dd>
                    </div>
                </dl>
            </div>

        </div>

        <!-- Seção de Valores e movimentos -->
        <div class="grid grid-cols-1 gap-2 mb-2">
            <!-- Adicionais -->
            @if ($aluguel->adicionaisAlugueis->isNotEmpty())
                <div class="p-2 border border-gray-200 bg-primary-50/30 rounded-xl">
                    <h5 class="font-bold text-gray-800 mb-2 flex items-center">
                        <x-heroicon-s-squares-plus class="w-6 h-6 mr-2 text-primary-600" /> Adicionais
                    </h5>
                    <dl class="space-y-2 text-sm">
                        <div class="pt-2 border-t border-gray-200">
                            <div class="pl-3 space-y-1">
                                @foreach ($aluguel->adicionaisAlugueis as $adicionalAluguel)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 text-[10px]">
                                            {{ $adicionalAluguel->adicional->descricao_adicional ?? 'Adicional' }}
                                            ({{ number_format($adicionalAluguel->quantidade_adicional_aluguel, 0) }}x)
                                        </span>
                                        <span class="text-gray-700 text-[10px]">
                                            R$
                                            {{ number_format($adicionalAluguel->valor_total_adicional_aluguel, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-1 font-semibold">
                                <span class="text-gray-700 text-[10px]">Total Adicionais:</span>
                                <span class="text-gray-800 text-[10px]">
                                    R$
                                    {{ number_format($aluguel->adicionaisAlugueis->sum('valor_total_adicional_aluguel') ?? 0, 2, ',', '.') }}
                                </span>

                            </div>
                        </div>

                    </dl>
                </div>
            @endif

            <!-- Resumo Financeiro -->
            <div class="p-2 border border-gray-200 bg-primary-50/30 rounded-xl">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center">
                    <x-heroicon-s-currency-dollar class="w-6 h-6 mr-2 text-primary-600" /> Resumo Financeiro
                </h5>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between font-medium text-gray-700 text-[10px]">
                        <dt>Valor Diária Carreta/Reboque:</dt>
                        <dd>R$ {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</dd>
                    </div>
                    @if ($aluguel->adicionaisAlugueis->isNotEmpty())
                        <div class="flex justify-between font-medium text-gray-700 text-[10px]">
                            <dt>Valor Diária Adicionais:</dt>
                            <dd>R$ {{ number_format($aluguel->valor_adicionais_aluguel ?? 0.0, 2, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between font-medium text-gray-700 text-[10px]">
                        <dt>Qtd de Diárias:</dt>
                        <dd>{{ $aluguel->quantidade_diarias }} dias</dd>
                    </div>
                    <div class="flex justify-between text-gray-700 text-[10px]">
                        <dt>(+)Acréscimo:</dt>
                        <dd>R$ {{ number_format($aluguel->valor_acrescimo_aluguel ?? 0.0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between text-gray-700 text-[10px]">
                        <dt>(-)Desconto:</dt>
                        <dd>R$ {{ number_format($aluguel->valor_desconto_aluguel ?? 0.0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-gray-200">
                        <dt class="text-gray-800">VALOR TOTAL:</dt>
                        <dd class="text-green-600">R$
                            {{ number_format($aluguel->valor_total_aluguel ?? 0.0, 2, ',', '.') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Histórico de movimentos -->
            <div class="p-2 border border-gray-200 bg-white rounded-xl">
                <h5 class="font-bold text-gray-700 mb-2 flex items-center">
                    <x-heroicon-s-banknotes class="w-5 h-5 mr-2 text-primary-500" /> Pagamentos
                </h5>

                @if ($aluguel->movimentos->count())
                    <ul class="space-y-2 text-[10px]">
                        @foreach ($aluguel->movimentos as $pagamento)
                            <li class="flex justify-between items-center border-b border-gray-100 pb-1 last:border-b-0">
                                <span class="text-gray-600">{{ $pagamento->created_at->format('d/m/Y') }} -
                                    {{ $pagamento->metodoPagamento->nome ?? 'Método não especificado' }}</span>
                                <span class="font-semibold text-green-700">R$
                                    {{ number_format($pagamento->valor_total_movimento, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between font-bold text-base mt-4 pt-2 border-t border-gray-200">
                        <dt class="text-gray-700">Total Pago:</dt>
                        <dd class="text-green-700">R$
                            {{ number_format($aluguel->movimentos->sum('valor_total_movimento'), 2, ',', '.') }}
                        </dd>
                    </div>
                @else
                    <p class="text-gray-500 italic">Nenhum pagamento registrado até o momento.</p>
                @endif
            </div>

        </div>

        <!-- Seção de Assinaturas -->
        <div class="grid grid-cols-2 gap-12 text-center mt-4">
            <!-- Locatário -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                <p class="font-semibold text-gray-800">{{ $aluguel->cliente->nome }}</p>
                <p class="text-sm text-gray-500">Locatário ({{ $aluguel->cliente->cpf_cnpj }})</p>
            </div>

            <!-- Locador -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                <p class="font-semibold text-gray-800">22.341.672 IVAN DE AQUINO SILVA - ME</p>
                <p class="text-sm text-gray-500">Locador (22.341.672/0001-01)</p>
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
