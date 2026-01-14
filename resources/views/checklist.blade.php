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

<body class="bg-gray-50">
    <div class="p-1 max-w-6xl mx-auto ring-1 ring-gray-100/70 bg-white">
        <!-- Header Principal e Logo -->
        <header
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-1 border-gray-200 pb-2 mb-2">
            <div class="space-y-1">
                <h5 class="text-lg font-extrabold text-gray-800">
                    CHECKLIST DE DEVOLUÇÃO
                </h5>
                <p class="text-xs text-gray-600">
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
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-xs">
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
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-xs">
                    <x-heroicon-s-user class="w-5 h-5 mr-2 text-primary-600" /> LOCATÁRIO
                </h5>
                <div class="space-y-1 text-xs text-gray-700 text-[10px]">
                    <p><strong>Nome:</strong> {{ $aluguel->cliente->nome }}</p>
                    <p><strong>CPF/CNPJ:</strong>
                        {{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }}</p>
                    <p><strong>Telefone:</strong> {{ $aluguel->cliente->telefone }}</p>
                    <p><strong>Endereço:</strong> {{ $aluguel->cliente->endereco ?? 'Não informado' }}</p>
                </div>
            </div>

            <!-- Veículo -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-xs">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-600" /> VEÍCULO LOCADO
                </h5>
                <div class="space-y-1 text-xs text-gray-700 text-[10px]">
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

        <!-- 2. CONDIÇÕES MECÂNICAS E ESTRUTURAIS -->
        <div class="p-3 mb-3 border-2 border-orange-300 bg-orange-50 rounded-lg">
            <h5 class="font-extrabold text-orange-800 mb-3 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                        clip-rule="evenodd" />
                </svg>
                2. CONDIÇÕES MECÂNICAS E ESTRUTURAIS
            </h5>

            <div class="space-y-4 text-xs">
                <!-- Pneus -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="p-3 bg-white border border-orange-200 rounded-lg">
                        <label class="font-bold text-gray-800 mb-2 block">Pneus:</label>
                        <div class="flex gap-6">
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

                    <div class="p-3 bg-white border border-orange-200 rounded-lg">
                        <label class="font-bold text-gray-800 mb-2 block">Estepe:</label>
                        <div class="flex gap-6">
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
                        <label class="font-bold text-gray-800 mb-2 block">Elétrica (lanternas, freios, pisca):</label>
                        <div class="flex gap-6">
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
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex-1">
                                <label class="font-bold text-gray-800 mb-2 block">Cintas:</label>
                                <div class="flex gap-6">
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
                        <div class="flex gap-6">
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
                    <textarea class="w-full border-2 border-gray-300 rounded p-2 min-h-16 text-xs"
                        placeholder=""></textarea>
                </div>
            </div>
        </div>

        <!-- 3. ASSINATURAS -->
        <div class="p-3 mb-3 border-2 border-green-300 bg-green-50 rounded-lg">
            <h5 class="font-extrabold text-green-800 mb-4 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                3. ASSINATURAS
            </h5>

            <p class="text-[9px] text-gray-800 text-justify mb-4 p-2 bg-yellow-50 border border-yellow-200 rounded">
                <strong>Declaração:</strong> Declaro que o veículo foi devolvido nas condições acima descritas e
                concordo com as informações registradas neste checklist.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-center">
                <!-- Colaborador -->
                <div class="p-3 bg-white border border-green-200 rounded-lg">
                    <label class="text-xs font-semibold text-gray-600 mb-3 block">Colaborador Responsável:</label>
                    <div class="border-b-2 border-gray-400 h-6 w-4/5 mx-auto mb-3"></div>
                    <div class="text-xs text-gray-500">
                        <p>Nome: ______________________________________________</p>
                        <p class="mt-1">Data: {{ \Carbon\Carbon::now()->format('d/m/Y') }} Hora: ___:___</p>
                    </div>
                </div>

                <!-- Locatário -->
                <div class="p-3 bg-white border border-green-200 rounded-lg">
                    <label class="text-xs font-semibold text-gray-600 mb-3 block">Locatário:</label>
                    <div class="border-b-2 border-gray-400 h-6 w-4/5 mx-auto mb-3"></div>
                    <div class="text-xs">
                        <p class="font-bold text-gray-800">{{ $aluguel->cliente->nome }}</p>
                        <p class="text-gray-600">
                            {{ \App\Helper\FormatHelper::formatCpfCnpj($aluguel->cliente->cpf_cnpj) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="pt-3 border-t-2 border-gray-200 text-center">
            <p class="text-xs text-gray-500 font-semibold">
                Checklist de Devolução | LocSilva - 22.341.672 IVAN DE AQUINO SILVA - ME
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

</html>
