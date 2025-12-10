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
                <p class="text-sm text-gray-600">
                    Contrato <span class="font-bold text-primary-600">#{{ $aluguel->id }}</span>
                </p>
                <p class="text-xs text-gray-500">
                    Emitido em: {{ \Carbon\Carbon::parse($aluguel->created_at)->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Informações da Empresa -->
        <div class="p-2 mb-2 bg-gray-50 border border-gray-200 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-building-office class="w-5 h-5 mr-2 text-primary-600" /> LOCADOR
            </h5>
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

        <!-- Grid: Cliente e Veículo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">

            <!-- Cliente -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                    <x-heroicon-s-user class="w-5 h-5 mr-2 text-primary-600" /> LOCATÁRIO
                </h5>
                <div class="space-y-1 text-sm text-gray-700 text-[10px]">
                    <p><strong>Nome:</strong> {{ $aluguel->cliente->nome }}</p>
                    <p><strong>CPF/CNPJ:</strong> {{ $aluguel->cliente->cpf_cnpj }}</p>
                    <p><strong>Telefone:</strong> {{ $aluguel->cliente->telefone }}</p>
                    <p><strong>Endereço:</strong> {{ $aluguel->cliente->endereco ?? 'Não informado' }}</p>
                </div>
            </div>

            <!-- Veículo -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-600" /> VEÍCULO LOCADO
                </h5>
                <div class="space-y-1 text-sm text-gray-700 text-[10px]">
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
        <div class="p-2 mb-2 border-1 border-gray-200 bg-primary-50/30 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-calendar class="w-5 h-5 mr-2 text-primary-600" /> PERÍODO DO ALUGUEL
            </h5>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[9px]">
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Data de Retirada</p>
                    <p class="font-bold text-green-700">
                        {{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('d/m/Y \à\s H:i') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Devolução Prevista</p>
                    <p class="font-bold text-orange-700">
                        {{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('d/m/Y \à\s H:i') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Quantidade de Diárias</p>
                    <p class="font-bold text-blue-700">{{ $aluguel->quantidade_diarias }} dia(s)</p>
                </div>
            </div>
        </div>

        <!-- Valores -->
        <div class="p-2 mb-2 border border-gray-200 bg-white rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-currency-dollar class="w-5 h-5 mr-2 text-green-600" /> VALORES
            </h5>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-700">Valor da Diária:</span>
                    <span class="font-semibold">R$
                        {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Quantidade de Diárias:</span>
                    <span class="font-semibold">{{ $aluguel->quantidade_diarias }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="font-bold text-gray-800">VALOR TOTAL PREVISTO:</span>
                    <span class="font-bold text-green-600 text-lg">
                        R$ {{ number_format($aluguel->valor_total_aluguel ?? 0.0, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- REGRAS E CONDIÇÕES - DESTAQUE -->
        <div class="p-2 mb-2 border-2 border-red-400 bg-red-50 rounded-lg">
            <h5 class="font-extrabold text-red-800 mb-2 text-center text-sm flex items-center justify-center">
                <x-heroicon-s-exclamation-triangle class="w-6 h-6 mr-2" />
                REGRAS E CONDIÇÕES IMPORTANTES
            </h5>

            <!-- GRID EM 2 COLUNAS -->
            <div class="grid grid-cols-2 gap-3 text-[8px] text-gray-800">

                <!-- COLUNA 1 -->
                <div class="space-y-2">

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
                        <p><strong>NÃO</strong> é aplicado nenhum desconto caso a devolução seja antes das 24 horas.</p>
                    </div>

                     <div class="flex items-start">
                        <span class="text-blue-600 font-bold mr-2">✓</span>
                        <p>Desconto <strong>somente a partir de 3 diárias.</strong></p>
                    </div>

                </div>

                <!-- COLUNA 2 -->
                <div class="space-y-2">

                    <div class="flex items-start">
                        <span class="text-blue-600 font-bold mr-2">✓</span>
                        <p>Precisa ficar muitos dias? <strong>É necessário combinar previamente.</strong></p>
                    </div>

                    <div class="flex items-start">
                        <span class="text-orange-600 font-bold mr-2">⚠</span>
                        <p>Na devolução, <strong>carreta com lixo terá taxa de R$ 10,00.</strong></p>
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


        <!-- Observações -->
        @if ($aluguel->observacoes)
            <div class="p-2 mb-2 border border-gray-200 bg-gray-50 rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2">OBSERVAÇÕES</h5>
                <p class="text-sm text-gray-700">{{ $aluguel->observacoes }}</p>
            </div>
        @endif

        <!-- Declaração e Assinaturas -->
        <div class="p-2 mb-2 border border-gray-300 bg-white rounded-lg">
            <p class="text-[9px] text-gray-800 text-justify mb-2">
                Declaro que recebi o veículo acima identificado em perfeitas condições de uso e funcionamento,
                comprometendo-me a devolvê-lo nas mesmas condições. Declaro ainda que li e concordo com todas as
                regras e condições estabelecidas neste recibo.
            </p>

            <div class="grid grid-cols-2 gap-12 text-center">
                <!-- Locatário -->
                <div>
                    <div class="border-b-2 border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-gray-800">{{ $aluguel->cliente->nome }}</p>
                    <p class="text-xs text-gray-600">
                        Locatário ({{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }})
                    </p>

                    <!--<p class="text-xs text-gray-500 mt-1">Data: _____/_____/_________</p>-->
                </div>

                <!-- Locador -->
                <div>
                    <div class="border-b-2 border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-gray-800">22.341.672 IVAN DE AQUINO SILVA - ME</p>
                    <p class="text-xs text-gray-600">Locador (22.341.672/0001-01)</p>
                    <!--<p class="text-xs text-gray-500 mt-1">Data: _____/_____/_________</p>-->
                </div>
            </div>
        </div>

        <footer class="pt-3 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                Este recibo não tem valor fiscal | Via do Cliente
            </p>
            <p class="text-xs text-gray-400">
                Documento gerado em {{ now()->format('d/m/Y \à\s H:i:s') }}
            </p>
        </footer>

    </div>

    <script>
        window.print();
    </script>
</body>
