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
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-1 border-gray-200 pb-2 mb-2">
            <div class="space-y-1">
                <h5 class="text-lg font-extrabold text-gray-800">
                    RECIBO DE RETIRADA
                </h5>
                <p class="text-xs text-gray-600">
                    Contrato <span class="font-bold text-primary-600">#{{ $aluguel->id }}</span> | Emitido em:
                    {{ \Carbon\Carbon::parse($aluguel->created_at)->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-10 mt-2 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Grid: Cliente e Veículo -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 mb-1">

            <!-- Informações da Empresa -->
            <div class="p-2 border bg-gray-200 border-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-[9px]">
                    <x-heroicon-s-building-office class="w-5 h-5 mr-2 text-primary-600" /> LOCADOR
                </h5>
                <div class="grid grid-cols-1 text-[10px] text-gray-700">
                    <div>
                        <p><strong>Empresa:</strong> 22.341.672 IVAN DE AQUINO SILVA - ME</p>
                        <p><strong>CNPJ:</strong> 22.341.672/0001-01</p>
                        <p><strong>Telefone:</strong> (62) 9 9323-9697</p>
                        <p><strong>Endereço:</strong> R. Maria Conceição, 245, Parque Amazonia, Goiânia - GO</p>
                    </div>
                </div>
            </div>

            <!-- Cliente -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-[9px]">
                    <x-heroicon-s-user class="w-5 h-5 mr-2 text-primary-600" /> LOCATÁRIO
                </h5>
                <div class="space-y-1 text-gray-700 text-[10px]">
                    <p><strong>Nome:</strong> {{ $aluguel->cliente->nome }}</p>
                    <p><strong>CPF/CNPJ:</strong>
                        {{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }}</p>
                    <p><strong>Telefone:</strong> {{ $aluguel->cliente->telefone }}</p>
                    <p><strong>Endereço:</strong> {{ $aluguel->cliente->endereco ?? 'Não informado' }}</p>
                </div>
            </div>

            <!-- Veículo -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-[9px]">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-600" /> VEÍCULO LOCADO
                </h5>
                <div class="space-y-1 text-gray-700 text-[10px]">
                    <p><strong>Identificação:</strong> {{ $aluguel->carreta->identificacao }}</p>
                    <p><strong>Placa:</strong>
                        <span class="font-mono text-base bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">
                            {{ $aluguel->carreta->placa }}
                        </span>
                    </p>
                    <p><strong>Descrição:</strong>
                        {{ $aluguel->carreta->marca . ' ' . $aluguel->carreta->modelo . ' ' . $aluguel->carreta->ano ?? ' N/A' }}
                    </p>
                    <p><strong>Capacidade de carga (kg):</strong>
                        {{ number_format($aluguel->carreta->capacidade_carga, 0) ?? 'N/A' }}</p>
                </div>
            </div>

        </div>

        <!-- Informações do Aluguel -->
        <div class="grid grid-cols-2 gap-1">

            <!-- Datas -->
            <div class="p-2 mb-2 border-1 border-gray-200 bg-primary-50/30 rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-[9px]">
                    <x-heroicon-s-calendar class="w-5 h-5 mr-2 text-primary-600" /> PERÍODO DO ALUGUEL
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[9px]">
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-[9px] text-gray-600 mb-1">Data de Retirada</p>
                        <p class="font-bold text-green-700">
                            {{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-[9px] text-gray-600 mb-1">Devolução Prevista</p>
                        <p class="font-bold text-orange-700">
                            {{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                    <div class="p-2 bg-white rounded border border-gray-200">
                        <p class="text-[9px] text-gray-600 mb-1">Quantidade de Diárias</p>
                        <p class="font-bold text-blue-700">{{ $aluguel->quantidade_diarias }} dia(s)</p>
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="p-2 mb-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-[9px]">
                    <x-heroicon-s-currency-dollar class="w-5 h-5 mr-2 text-green-600" /> VALORES
                </h5>
                <div class="space-y-1 text-[9px]">
                    <div class="flex justify-between">
                        <span class="text-gray-700 text-[10px]">Valor da Diária:</span>
                        <span class="font-semibold text-[10px]">R$
                            {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700 text-[10px]">Quantidade de Diárias:</span>
                        <span class="font-semibold text-[10px]">{{ $aluguel->quantidade_diarias }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700 text-[10px]">Subtotal Diárias:</span>
                        <span class="font-semibold text-[10px]">R$
                            {{ number_format($aluguel->carreta->valor_diaria * $aluguel->quantidade_diarias, 2, ',', '.') }}</span>
                    </div>

                    <!-- Adicionais -->
                    @if ($aluguel->adicionaisAlugueis->isNotEmpty())
                        <div class="pt-2 border-t border-gray-200">
                            <div class="flex justify-between mb-1">
                                <span class="font-semibold text-[10px] text-gray-800">Adicionais:</span>
                            </div>
                            <div class="pl-3 space-y-1">
                                @foreach ($aluguel->adicionaisAlugueis as $adicionalAluguel)
                                    <div class="flex justify-between text-[9px]">
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
                    @endif

                    <!-- Acréscimos e Descontos -->
                    @if ($aluguel->valor_acrescimo_aluguel > 0 || $aluguel->valor_desconto_aluguel > 0)
                        <div class="pt-2 border-t border-gray-200 space-y-1">
                            @if ($aluguel->valor_acrescimo_aluguel > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-700 text-[10px]">Acréscimos:</span>
                                    <span class="font-semibold text-[10px] text-orange-600">+ R$
                                        {{ number_format($aluguel->valor_acrescimo_aluguel, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($aluguel->valor_desconto_aluguel > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-700 text-[10px]">Descontos:</span>
                                    <span class="font-semibold text-[10px] text-blue-600">- R$
                                        {{ number_format($aluguel->valor_desconto_aluguel, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Total Final -->
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="font-bold text-[10px] text-gray-800">VALOR TOTAL:</span>
                        <span class="font-bold text-[10px] text-green-600 text-lg">
                            R$ {{ number_format($aluguel->valor_total_aluguel ?? 0.0, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-700 text-[10px]">Valor Pago:</span>
                        <span class="font-semibold text-[10px] text-green-600">R$
                            {{ number_format($aluguel->valor_pago_aluguel ?? 0.0, 2, ',', '.') }}</span>
                    </div>

                    @if (($aluguel->valor_saldo_aluguel ?? 0) > 0)
                        <div class="flex justify-between pt-1">
                            <span class="text-gray-700 text-[10px]">Saldo Restante:</span>
                            <span class="font-semibold text-[10px] text-red-600">R$
                                {{ number_format($aluguel->valor_saldo_aluguel ?? 0.0, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1">
            <!-- 2. CONDIÇÕES MECÂNICAS E ESTRUTURAIS -->
            <div class="p-2 mb-2 border-2 border-orange-300 bg-orange-50 rounded-lg">
                <h5 class="font-extrabold text-orange-800 mb-1 text-[9px] flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                            clip-rule="evenodd" />
                    </svg>
                    CONDIÇÕES MECÂNICAS E ESTRUTURAIS
                </h5>

                <div class="space-y-2 text-[9px]">
                    <!-- Pneus -->
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                        <div class="p-3 bg-white border border-orange-200 rounded-lg">
                            <label class="font-bold text-gray-800 mb-2 block">Pneus:</label>
                            <div class="flex flex-col gap-1">
                                <label class="flex-rows cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">OK</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">Irregular</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-3 bg-white border border-orange-200 rounded-lg">
                            <label class="font-bold text-gray-800 mb-2 block">Estepe:</label>
                            <div class="flex flex-col gap-1">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">OK</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">Ausente</span>
                                </label>
                            </div>
                        </div>


                        <!-- Elétrica -->
                        <div class="p-3 bg-white border border-orange-200 rounded-lg">
                            <label class="font-bold text-gray-800 mb-2 block">Elétrica (lanternas, freios,
                                pisca):</label>
                            <div class="flex flex-col gap-1">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">OK</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">Irregular</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cintas -->
                        <div class="p-3 bg-white border border-orange-200 rounded-lg">
                            <div class="flex flex-col gap-2">
                                <div class="flex-1">
                                    <label class="font-bold text-gray-800 mb-2 block">Cintas:</label>
                                    <div class="flex flex-col gap-1">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                            <span class="font-semibold">OK</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="font-semibold text-gray-700">Quantidade:</label>
                                    <div class="border-b-2 border-gray-400 w-20 text-center pb-1">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Limpeza -->
                        <div class="p-3 bg-white border border-orange-200 rounded-lg">
                            <label class="font-bold text-gray-800 mb-2 block">Limpeza da Carreta:</label>
                            <div class="flex flex-col gap-1">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold">OK - Limpa</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 mr-2 border-2 border-gray-400">
                                    <span class="font-semibold text-red-600">Com Lixo (Taxa R$ 20,00)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Observações -->
                    <div class="p-3 bg-white border border-orange-200 rounded-lg">
                        <label class="font-bold text-gray-800 mb-2 block">Observações / Avarias:</label>
                        <textarea class="w-full border-2 border-gray-300 rounded p-2 min-h-16 text-xs" placeholder=""></textarea>
                    </div>
                </div>
            </div>
            <!-- REGRAS E CONDIÇÕES - DESTAQUE -->
            <div class="p-2 mb-2 border-2 border-red-400 bg-red-50 rounded-lg">
                <h5 class="font-extrabold text-red-800 text-center text-xs flex items-center justify-center">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-2" />
                    REGRAS E CONDIÇÕES IMPORTANTES
                </h5>

                <!-- GRID EM 2 COLUNAS -->
                <div class="grid grid-cols-2 gap-3 text-[8px] text-gray-800">

                    <!-- COLUNA 1 -->
                    <div class="space-y-1">

                        <div class="flex items-start">
                            <span class="text-red-600 font-bold mr-2">•</span>
                            <p><strong>Horário de funcionamento: SEG-SEX 08h–12h, 13h-18h e SÁB 08h–12h</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-red-600 font-bold mr-2">•</span>
                            <p><strong>NÃO recebemos carreta em horário de almoço (12h-13h) ou fora do horário
                                    comercial.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-red-600 font-bold mr-2">•</span>
                            <p><strong>NÃO é permitido deixar a carreta sem dar baixa.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-red-600 font-bold mr-2">•</span>
                            <p><strong>NÃO atendemos após ou antes do horário comercial.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <p>A sua diária tem <strong>24 horas + 20 minutos de tolerância.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <p><strong>NÃO</strong> é aplicado nenhum desconto caso a devolução seja antes das 24 horas.
                            </p>
                        </div>



                    </div>

                    <!-- COLUNA 2 -->
                    <div class="space-y-1">

                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <p>Desconto <strong>somente a partir de 3 diárias.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <p>Precisa ficar muitos dias? <strong>É necessário combinar previamente.</strong></p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-orange-600 font-bold mr-2">⚠</span>
                            <p>Na devolução, <strong>carreta com lixo terá taxa de R$ 20,00.</strong></p>
                        </div>

                        <!-- 🆕 NOVAS REGRAS ADICIONADAS -->
                        <div class="flex items-start">
                            <span class="text-purple-600 font-bold mr-2">*</span>
                            <p>
                                <strong>Nossas diárias funcionam assim:</strong><br>
                                <strong>Seg a Sex:</strong> 24h (exceto sexta após 12h — devido ao horário de sábado:
                                08h–12h).<br>
                                <strong>Sábado:</strong> aluguel somente para devolução no mesmo dia até 12h ou para 2
                                diárias.
                            </p>
                        </div>

                        <div class="flex items-start">
                            <span class="text-purple-600 font-bold mr-2">*</span>
                            <p>
                                <strong>Tempo de tolerância:</strong> 20 minutos (dentro do horário comercial).
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- Observações -->
        @if ($aluguel->observacoes)
            <div class="p-2 mb-2 border border-gray-200 bg-gray-50 rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2">OBSERVAÇÕES</h5>
                <p class="text-xs text-gray-700">{{ $aluguel->observacoes }}</p>
            </div>
        @endif

        <!-- Declaração e Assinaturas -->
        <div class="p-2 mb-1 border border-gray-300 bg-white rounded-lg">
            <p class="text-[9px] text-gray-800 text-justify mb-2">
                Declaro que recebi o veículo acima identificado em perfeitas condições de uso e funcionamento,
                comprometendo-me a devolvê-lo nas mesmas condições. Declaro ainda que li e concordo com todas as
                regras e condições estabelecidas neste recibo.
            </p>

            <div class="grid grid-cols-2 gap-12 text-center">
                <!-- Locatário -->
                <div>
                    <div class="border-b-2 border-gray-400 h-6 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-sm text-gray-800">{{ $aluguel->cliente->nome }}</p>
                    <p class="text-[9px] text-gray-600">
                        Locatário ({{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }})
                    </p>

                    <!--<p class="text-xs text-gray-500 mt-1">Data: _____/_____/_________</p>-->
                </div>

                <!-- Locador -->
                <div>
                    <div class="border-b-2 border-gray-400 h-6 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-sm text-gray-800">22.341.672 IVAN DE AQUINO SILVA - ME</p>
                    <p class="text-[9px] text-gray-600">Locador (22.341.672/0001-01)</p>
                    <!--<p class="text-xs text-gray-500 mt-1">Data: _____/_____/_________</p>-->
                </div>
            </div>
        </div>

        <footer class="pt-1 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                Este recibo não tem valor fiscal | Via do Cliente | Documento gerado em
                {{ now()->format('d/m/Y \à\s H:i:s') }}
            </p>
        </footer>

    </div>

    <script>
        window.print();
    </script>
</body>
