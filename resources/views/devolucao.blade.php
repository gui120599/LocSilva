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
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-1 border-gray-200 pb-2 mb-2">
            <div class="space-y-1">
                <h5 class="text-lg font-extrabold text-gray-800">
                    RECIBO DE DEVOLUÇÃO
                </h5>
                <p class="text-sm text-gray-600">
                    Aluguel <span class="font-bold text-primary-600">#{{ $aluguel->id }}</span>
                </p>
                <p class="text-xs text-gray-500">
                    Emitido em: {{ now()->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Informações Básicas -->
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
                </div>
            </div>

            <!-- Veículo -->
            <div class="p-2 border border-gray-200 bg-white rounded-lg">
                <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-600" /> VEÍCULO DEVOLVIDO
                </h5>
                <div class="space-y-1 text-sm text-gray-700 text-[10px]">
                    <p><strong>Identificação:</strong> {{ $aluguel->carreta->identificacao }}</p>
                    <p><strong>Placa:</strong> 
                        <span class="font-mono text-base bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">
                            {{ $aluguel->carreta->placa }}
                        </span>
                    </p>
                </div>
            </div>

        </div>

        <!-- Período e Cálculo -->
        <div class="p-2 mb-2 border-1 border-gray-200 bg-blue-50/30 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-clock class="w-5 h-5 mr-2 text-blue-600" /> PERÍODO E CÁLCULO
            </h5>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-[9px]">
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Retirada</p>
                    <p class="font-bold text-gray-800">
                        {{ \Carbon\Carbon::parse($aluguel->data_retirada)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Devolução Prevista</p>
                    <p class="font-bold text-orange-600">
                        {{ \Carbon\Carbon::parse($aluguel->data_devolucao_prevista)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Devolução Real</p>
                    <p class="font-bold text-green-600">
                        {{ $aluguel->data_devolucao_real ? \Carbon\Carbon::parse($aluguel->data_devolucao_real)->format('d/m/Y H:i') : 'A definir' }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded border border-blue-200">
                    <p class="text-xs text-gray-600 mb-1">Diárias Cobradas</p>
                    <p class="font-bold text-blue-700 text-lg">{{ $aluguel->quantidade_diarias }}</p>
                </div>
            </div>
        </div>

        <!-- CHECKLIST DE INSPEÇÃO -->
        <div class="p-2 mb-2 border-2 border-gray-300 bg-gray-50 rounded-lg">
            <h5 class="font-extrabold text-gray-800 mb-2 text-center text-sm flex items-center justify-center">
                <x-heroicon-s-clipboard-document-check class="w-6 h-6 mr-2 text-blue-600" /> 
                CHECKLIST DE INSPEÇÃO DO VEÍCULO
            </h5>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Coluna 1 -->
                <div class="space-y-2 text-[8px]">
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Veículo limpo (sem lixo)</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Pneus em bom estado</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Lanternas funcionando</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Engate em perfeito estado</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Freios funcionando</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Estepe presente</span>
                    </div>
                </div>
                
                <!-- Coluna 2 -->
                <div class="space-y-2 text-[8px]">
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Lataria sem avarias</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Assoalho/piso intacto</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Correntes/cintas presentes</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Documentação presente</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Triângulo de segurança</span>
                    </div>
                    
                    <div class="flex items-center border-b border-gray-300 pb-2">
                        <input type="checkbox" class="w-5 h-5 mr-3 border-2 border-gray-400" />
                        <span class="font-semibold">Sem danos estruturais</span>
                    </div>
                </div>
            </div>

            <!-- Observações sobre avarias -->
            <div class="mt-4 p-2 bg-white border border-gray-300 rounded">
                <p class="font-bold text-[9px] text-gray-800 mb-2">AVARIAS/OBSERVAÇÕES:</p>
                <div class="border-b border-gray-300 mb-2 pb-1 min-h-[30px]"></div>
                <div class="border-b border-gray-300 mb-2 pb-1 min-h-[30px]"></div>
                <div class="border-b border-gray-300 pb-1 min-h-[30px]"></div>
            </div>
        </div>

        <!-- Cobranças Adicionais -->
        <div class="p-2 mb-2 border border-orange-300 bg-orange-50 rounded-lg">
            <h5 class="font-bold text-orange-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-exclamation-circle class="w-5 h-5 mr-2" /> 
                COBRANÇAS ADICIONAIS
            </h5>
            <div class="space-y-2 text-[9px]">
                <div class="flex justify-between items-center p-2 bg-white rounded border border-gray-200">
                    <div class="flex items-center">
                        <input type="checkbox" class="w-4 h-4 mr-3" />
                        <span>Taxa de Limpeza (Lixo no veículo)</span>
                    </div>
                    <span class="font-bold text-orange-600">R$ 10,00</span>
                </div>
                
                <div class="flex justify-between items-center p-2 bg-white rounded border border-gray-200">
                    <div class="flex items-center">
                        <input type="checkbox" class="w-4 h-4 mr-3" />
                        <span>Diária(s) Extra(s) (Atraso na devolução)</span>
                    </div>
                    <span class="font-bold text-orange-600">R$ _______</span>
                </div>
                
                <div class="flex justify-between items-center p-2 bg-white rounded border border-gray-200">
                    <div class="flex items-center">
                        <input type="checkbox" class="w-4 h-4 mr-3" />
                        <span>Reparos/Danos</span>
                    </div>
                    <span class="font-bold text-orange-600">R$ _______</span>
                </div>
            </div>
        </div>

        <!-- Resumo Financeiro Final -->
        <div class="p-2 mb-2 border-2 border-green-300 bg-green-50 rounded-lg">
            <h5 class="font-bold text-gray-800 mb-2 flex items-center text-sm">
                <x-heroicon-s-calculator class="w-5 h-5 mr-2 text-green-600" /> 
                RESUMO FINANCEIRO
            </h5>
            <div class="space-y-2 text-[9px]">
                <div class="flex justify-between">
                    <span>Valor das Diárias:</span>
                    <span class="font-semibold">R$ {{ number_format($aluguel->valor_total_aluguel ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cobranças Adicionais:</span>
                    <span class="font-semibold text-orange-600">R$ _______</span>
                </div>
                <div class="flex justify-between">
                    <span>Descontos Aplicados:</span>
                    <span class="font-semibold text-blue-600">- R$ {{ number_format($aluguel->valor_desconto_aluguel ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                    <span class="font-bold text-lg">VALOR TOTAL:</span>
                    <span class="font-bold text-green-700 text-xl">R$ _______</span>
                </div>
                <div class="flex justify-between text-green-700">
                    <span class="font-bold">Valor Pago:</span>
                    <span class="font-bold">R$ {{ number_format($aluguel->movimentos->sum('valor_total_movimento'), 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-300">
                    <span class="font-bold text-lg">SALDO:</span>
                    <span class="font-bold text-blue-700 text-xl">R$ _______</span>
                </div>
            </div>
        </div>

        <!-- Declaração e Assinaturas -->
        <div class="p-2 mb-2 border border-gray-300 bg-white rounded-lg">
            <p class="text-[9px] text-gray-800 text-justify mb-2">
                Declaro que devolvi o veículo acima identificado conforme verificado no checklist de inspeção, 
                estando ciente de eventuais cobranças adicionais por avarias ou taxas aplicáveis. 
                Confirmo que todas as informações acima estão corretas.
            </p>
            
            <div class="grid grid-cols-2 gap-12 text-center">
                <!-- Locatário -->
                <div>
                    <div class="border-b-2 border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-gray-800">{{ $aluguel->cliente->nome }}</p>
                    <p class="text-xs text-gray-600">Locatário ({{ $aluguel->cliente->cpf_cnpj }})</p>
                </div>

                <!-- Locador -->
                <div>
                    <div class="border-b-2 border-gray-400 h-16 w-3/4 mx-auto mb-2"></div>
                    <p class="font-bold text-gray-800">Responsável pela Vistoria</p>
                    <p class="text-xs text-gray-600">22.341.672 IVAN DE AQUINO SILVA - ME</p>
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