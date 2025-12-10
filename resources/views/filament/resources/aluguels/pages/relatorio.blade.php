<x-filament-panels::page>
    <div class="mx-auto p-6">
        <div class="dark:bg-gray-800 dark:border-gray-600 rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold  flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Relatório de Aluguéis
                </h2>
                <p class="text-gray-600 mt-1">Selecione os filtros desejados para gerar o relatório</p>
            </div>

            <!-- Formulário de Filtros -->
            <form action="{{ route('relatorios.gerar-alugueis') }}" method="POST" target="_blank">
                @csrf

                <!-- Período -->
                <div class="mb-6 p-4 rounded-lg border border-blue-200">
                    <h3 class="font-bold  mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Período *
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium  mb-1">
                                Data Início
                            </label>
                            <input type="date" name="data_inicio" id="data_inicio"
                                value="{{ old('data_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                            @error('data_inicio')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="data_fim" class="block text-sm font-medium  mb-1">
                                Data Fim
                            </label>
                            <input type="date" name="data_fim" id="data_fim"
                                value="{{ old('data_fim', now()->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                            @error('data_fim')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tipo_data" class="block text-sm font-medium  mb-1">
                                Tipo de Data
                            </label>
                            <select name="tipo_data" id="tipo_data"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="retirada" {{ old('tipo_data') == 'retirada' ? 'selected' : '' }}>
                                    Data de Retirada
                                </option>
                                <option value="devolucao_prevista"
                                    {{ old('tipo_data') == 'devolucao_prevista' ? 'selected' : '' }}>
                                    Devolução Prevista
                                </option>
                                <option value="devolucao_real"
                                    {{ old('tipo_data') == 'devolucao_real' ? 'selected' : '' }}>
                                    Devolução Real
                                </option>
                                <option value="criacao" {{ old('tipo_data') == 'criacao' ? 'selected' : '' }}>
                                    Data de Criação
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Filtros Opcionais -->
                <div class="mb-6 p-4 rounded-lg border border-gray-200">
                    <h3 class="font-bold mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtros Opcionais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium  mb-1">
                                Status
                            </label>
                            <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Todos</option>
                                <option value="ativo" {{ old('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="finalizado" {{ old('status') == 'finalizado' ? 'selected' : '' }}>
                                    Finalizado</option>
                                <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>
                                    Cancelado</option>
                            </select>

                        </div>

                        <div>
                            <label for="cliente_id" class="block text-sm font-medium  mb-1">
                                Cliente
                            </label>
                            <select name="cliente_id" id="cliente_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Todos os Clientes</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome }} - {{ \App\Helper\FormatHelper::formatCpfCnpj($cliente->cpf_cnpj) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="carreta_id" class="block text-sm font-medium  mb-1">
                                Carreta
                            </label>
                            <select name="carreta_id" id="carreta_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Todas as Carretas</option>
                                @foreach ($carretas as $carreta)
                                    <option value="{{ $carreta->id }}"
                                        {{ old('carreta_id') == $carreta->id ? 'selected' : '' }}>
                                        {{ $carreta->identificacao }} - {{ $carreta->placa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-wrap gap-3 justify-end">
                    <!--<button type="button" onclick="document.getElementById('relatorioForm').reset();"
                        class="px-6 py-2 bg-gray-200  rounded-lg hover:bg-gray-300 transition duration-200 flex items-center cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Limpar
                    </button>-->

                    <button type="submit" formaction="{{ route('relatorios.gerar-alugueis') }}"
                        class="px-6 py-2 bg-primary-600 rounded-lg hover:bg-primary-700 transition duration-200 flex items-center cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Gerar Relatório
                    </button>

                    <!-- Botão PDF (opcional) -->
                    <!--
                <button type="submit"
                        formaction="{{ route('relatorios.exportar-pdf-alugueis') }}"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar PDF
                </button>
                -->
                </div>

                <p class="text-xs mt-4 text-center">
                    * Campos obrigatórios | O relatório será aberto em uma nova aba
                </p>
            </form>
        </div>

        <!-- Dicas de Uso -->
        <div class="mt-6 p-4 border border-blue-200 dark:bg-gray-800 dark:border-gray-600 rounded-lg">
            <h4 class="font-bold text-blue-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Dicas de Uso
            </h4>
            <ul class="text-sm space-y-1 list-disc list-inside">
                <li>Selecione o período e o tipo de data que deseja analisar</li>
                <li>Use os filtros opcionais para refinar sua busca</li>
                <li>O relatório será aberto em uma nova aba para impressão</li>
                <li>Deixe os filtros vazios para incluir todos os registros</li>
            </ul>
        </div>
    </div>

    <script>
        // Validação de datas
        document.getElementById('data_fim').addEventListener('change', function() {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = this.value;

            if (dataInicio && dataFim && dataFim < dataInicio) {
                alert('A data fim não pode ser anterior à data início');
                this.value = dataInicio;
            }
        });
    </script>
</x-filament-panels::page>
