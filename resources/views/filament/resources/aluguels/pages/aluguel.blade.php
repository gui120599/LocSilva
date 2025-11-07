<x-filament-panels::page>
    <div class="bg-white shadow-xl rounded-2xl p-8 max-w-6xl mx-auto ring-1 ring-gray-100/70">
        <!-- Header Principal e Logo -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-6 mb-6">
            <div class="space-y-1">
                <h1 class="text-3xl font-extrabold text-gray-800">
                    Contrato de Aluguel <span class="text-primary-600">#{{$aluguel->id}}</span>
                </h1>
                <p class="text-sm text-gray-500">
                    Emitido em: {{ \Carbon\Carbon::parse($aluguel->created_at)->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <!-- A logo foi movida para o canto direito superior com um estilo mais limpo -->
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva" class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Seção de Detalhes (Grid 3 Colunas) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">

            <!-- Card 1: Locatário (Cliente) -->
            <div class="p-5 border border-gray-100 bg-gray-50/50 rounded-xl shadow-sm hover:shadow-md transition duration-300">
                <h2 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-user class="w-5 h-5 mr-2 text-primary-500" /> Locatário
                </h2>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Nome:</strong> {{$aluguel->cliente->nome}}</p>
                    <p><strong>CPF/CNPJ:</strong> {{$aluguel->cliente->cpf_cnpj}}</p>
                    <p><strong>Telefone:</strong> {{$aluguel->cliente->telefone}}</p>
                    <p><strong>Endereço:</strong> {{$aluguel->cliente->endereco ?? 'Não informado'}}</p>
                </div>
            </div>

            <!-- Card 2: Locador (Sua Empresa) -->
            <div class="p-5 border border-gray-100 bg-gray-50/50 rounded-xl shadow-sm hover:shadow-md transition duration-300">
                <h2 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-building-office-2 class="w-5 h-5 mr-2 text-primary-500" /> Locador
                </h2>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Empresa:</strong> LocSilva Locações LTDA</p>
                    <p><strong>CNPJ:</strong> 11.111.111/0001-11</p>
                    <p><strong>Telefone:</strong> (62) 9 9999-9999</p>
                    <p><strong>Endereço:</strong> R. Maria Conceição, 245...</p>
                    <p class="text-xs text-gray-400">Parque Amazonia, Goiânia - GO, 74840-750</p>
                </div>
            </div>

            <!-- Card 3: Carreta/Reboque -->
            <div class="p-5 border border-gray-100 bg-gray-50/50 rounded-xl shadow-sm hover:shadow-md transition duration-300">
                <h2 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-truck class="w-5 h-5 mr-2 text-primary-500" /> Reboque
                </h2>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Identificação:</strong> {{$aluguel->carreta->identificacao}}</p>
                    <p><strong>Placa:</strong> <span class="font-mono text-base bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">{{$aluguel->carreta->placa}}</span></p>
                    <p><strong>Valor Diária:</strong> <span class="font-semibold text-green-600">R$ {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</span></p>
                    <!-- Adicione aqui mais informações relevantes do veículo, se houver -->
                </div>
            </div>
        </div>

        <!-- NOVO: Seção de Valores e movimentos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8 border-t border-gray-200 pt-6">

            <!-- Resumo Financeiro -->
            <div class="md:col-span-1 p-5 border border-gray-200 bg-white rounded-xl shadow-lg">
                <h2 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-currency-dollar class="w-5 h-5 mr-2 text-primary-500" /> Resumo Financeiro
                </h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between font-medium text-gray-700">
                        <dt>Valor da Diária:</dt>
                        <dd>R$ {{ number_format($aluguel->carreta->valor_diaria, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between font-medium text-gray-700">
                        <dt>Qtd de Diárias:</dt>
                        <dd>{{ $aluguel->quantidade_diarias }} dias</dd>
                    </div>
                    <!-- Simulando total do aluguel. Ajuste o campo conforme o seu modelo ($aluguel->valor_total) -->
                    <div class="flex justify-between text-gray-700">
                        <dt>(+)Acréscimo:</dt>
                        <dd>R$ {{ number_format($aluguel->valor_acrescimo_aluguel ?? 0.00, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <dt>(-)Desconto:</dt>
                        <dd>R$ {{ number_format($aluguel->valor_desconto_aluguel ?? 0.00, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-200">
                        <dt class="text-gray-800">VALOR TOTAL DO ALUGUEL:</dt>
                        <dd class="text-secondary-600">R$ {{ number_format($aluguel->valor_total_aluguel ?? 0.00, 2, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Histórico de movimentos -->
            <div class="md:col-span-2 p-5 border border-gray-200 bg-white rounded-xl shadow-lg">
                <h2 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                    <x-heroicon-s-banknotes class="w-5 h-5 mr-2 text-primary-500" /> Resumo Financeiro
                </h2>

                @if($aluguel->movimentos->count())
                    <ul class="space-y-2 text-sm">
                        @foreach($aluguel->movimentos as $pagamento)
                            <li class="flex justify-between items-center border-b border-gray-100 pb-1 last:border-b-0">
                                <span class="text-gray-600">{{ $pagamento->created_at->format('d/m/Y') }} - {{ $pagamento->metodoPagamento->nome ?? 'Método não especificado' }}</span>
                                <span class="font-semibold text-green-700">R$ {{ number_format($pagamento->valor_total_movimento, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between font-bold text-base mt-4 pt-2 border-t border-gray-200">
                        <dt class="text-gray-700">Total Pago:</dt>
                        <dd class="text-green-700">R$ {{ number_format($aluguel->movimentos->sum('valor_total_movimento'), 2, ',', '.') }}</dd>
                    </div>
                @else
                    <p class="text-gray-500 italic">Nenhum pagamento registrado até o momento.</p>
                @endif
            </div>

        </div>

        <!-- NOVO: Seção de Assinaturas -->
        <div class="mt-12 pt-8 border-t border-gray-200 grid grid-cols-2 gap-12 text-center">
            <!-- Locatário -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2">
                    <!-- Espaço para Assinatura do Locatário -->
                </div>
                <p class="font-semibold text-gray-800">{{$aluguel->cliente->nome}}</p>
                <p class="text-sm text-gray-500">Locatário ({{$aluguel->cliente->cpf_cnpj}})</p>
            </div>

            <!-- Locador -->
            <div>
                <div class="border-b border-gray-400 h-16 w-3/4 mx-auto mb-2">
                    <!-- Espaço para Assinatura do Locador -->
                </div>
                <p class="font-semibold text-gray-800">LocSilva Locações LTDA</p>
                <p class="text-sm text-gray-500">Locador (11.111.111/0001-11)</p>
            </div>
        </div>

        <footer class="mt-8 pt-4 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-400">Este é um documento interno gerado pelo sistema Filament.</p>
        </footer>

    </div>
</x-filament-panels::page>
