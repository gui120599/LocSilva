<?php

namespace App\Filament\Resources\Aluguels\Schemas;

use App\Filament\Tables\CarretasTable;
use App\Models\Carreta;
use App\Models\MetodoPagamento;
use App\Services\IBGEServices;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentPtbrFormFields\Money;

class AluguelForm
{
    public static function getCleanOptionString(Model $model): string
    {
        return new HtmlString(
            view('filament.components.select-user-results')
                ->with('name', $model?->identificacao)
                ->with('email', $model?->valor_diaria)
                ->with('image', $model?->foto)
                ->render()
        );
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    Step::make('Cliente')
                        ->schema([
                            Section::make()
                                ->description('Selecione o cliente para o aluguel')
                                ->icon('heroicon-s-user-group')
                                ->columns(1)
                                ->columnSpan(1)
                                ->schema([
                                    Select::make('cliente_id')->suffixIcon('heroicon-o-user-group')
                                        ->relationship('cliente', 'nome', fn(Builder $query) => $query->limit(20))
                                        ->createOptionForm([
                                            Section::make()
                                                ->description('Dados do cliente')
                                                ->icon('heroicon-s-user-circle')
                                                ->columns(4)
                                                ->schema([
                                                    Section::make()
                                                        ->columnSpan(3)
                                                        ->schema([
                                                            TextInput::make('nome')
                                                                ->autocomplete(false)
                                                                ->columnSpan(3)
                                                                ->required(),
                                                            TextInput::make('cpf_cnpj')
                                                                ->required()
                                                                ->label('CPF/CNPJ')
                                                                ->autocomplete(false)
                                                                ->dehydrateStateUsing(fn(string $state) => preg_replace("/\D/", "", $state))
                                                                ->mask(RawJs::make(
                                                                    <<<'JS'
                                                                    $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                                                    JS
                                                                ))
                                                                ->disabled(fn(string $operation): bool => $operation === 'edit')
                                                                ->rules([ //Não funciona o unique() pq ele verifica com a mask e na hora que salva no banco ele salva sem a mask
                                                                    'required',
                                                                    'cpf_ou_cnpj',
                                                                    fn($get, $record) => function ($attribute, $value, $fail) use ($record) {
                                                                        // Remove formatação
                                                                        $cpfCnpj = preg_replace("/\D/", "", $value);

                                                                        // Verifica se já existe
                                                                        $exists = \App\Models\Cliente::where('cpf_cnpj', $cpfCnpj)
                                                                            ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                                                            ->exists();

                                                                        if ($exists) {
                                                                            $fail('Este CPF/CNPJ já está cadastrado.');
                                                                        }
                                                                    },
                                                                ])
                                                                ->columnSpan(2),
                                                            DatePicker::make('data_nascimento'),
                                                        ])
                                                        ->columns(3),
                                                    Section::make()
                                                        ->columnSpan(1)
                                                        ->schema([
                                                            FileUpload::make('foto')
                                                        ]),
                                                ]),
                                            Section::make()
                                                ->description('Contato do cliente')
                                                ->icon('heroicon-s-phone')
                                                ->columns(2)
                                                ->schema([
                                                    TextInput::make('telefone')
                                                        ->dehydrateStateUsing(fn(string $state) => preg_replace("/\D/", "", $state))
                                                        ->mask('(99)9 9999-9999')
                                                        ->tel()
                                                        ->required(),
                                                    TextInput::make('email')
                                                        ->email(),
                                                ]),
                                            Section::make()
                                                ->description('Documentos do CLiente')
                                                ->icon('heroicon-s-paper-clip')
                                                ->columns(2)
                                                ->schema([
                                                    FileUpload::make('documento')
                                                        ->disk('public')
                                                        ->directory('documentos_clientes')
                                                        ->maxSize(2048)
                                                        ->hint('Tamanho máximo: 2MB')
                                                        ->downloadable()
                                                        ->openable(),
                                                    FileUpload::make('nota_promissoria')
                                                        ->label('Nota Promssória')
                                                        ->disk('public')
                                                        ->directory('promissorias_clientes')
                                                        ->maxSize(2048)
                                                        ->hint('Tamanho máximo: 2MB')
                                                        ->downloadable()
                                                        ->openable(),
                                                ]),
                                            Section::make()
                                                ->description('Endereço do cliente')
                                                ->icon('heroicon-s-map-pin')
                                                ->columns(6)
                                                ->schema([
                                                    TextInput::make('cep')
                                                        ->mask('99999-999')
                                                        ->live() // Garante que as mudanças no campo disparem a ação.
                                                        ->afterStateUpdated(function ($state, callable $set) {
                                                            // Limpa o CEP para conter apenas números.
                                                            $cepLimpo = preg_replace('/[^0-9]/', '', $state);
                                                            if (strlen($cepLimpo) === 8) {

                                                                $dadosEndereco = IBGEServices::buscaCep($cepLimpo);

                                                                if ($dadosEndereco) {
                                                                    $set('endereco', $dadosEndereco['logradouro'] ?? '');
                                                                    $set('bairro', $dadosEndereco['bairro'] ?? '');
                                                                    $set('estado', $dadosEndereco['uf'] ?? '');
                                                                    $set('cidade', $dadosEndereco['localidade'] ?? '');
                                                                } else {
                                                                    // Opcional: Limpar campos se a busca falhar
                                                                    $set('endereco', '');
                                                                    $set('bairro', '');
                                                                    $set('estado', '');
                                                                    $set('cidade', '');
                                                                    // Opcional: Adicionar uma notificação de erro
                                                                }
                                                            }
                                                        }),
                                                    TextInput::make('endereco')
                                                        ->columnSpan(3)
                                                        ->label('Logradouro'),
                                                    TextInput::make('complemento_endereco')
                                                        ->columnSpan(3)
                                                        ->label('Complemento'),
                                                    TextInput::make('bairro')
                                                        ->columnSpan(2),
                                                    Select::make('estado')
                                                        ->live()
                                                        ->preload(false)
                                                        ->options(IBGEServices::ufs())
                                                        ->searchable()
                                                        ->columnSpan(3),
                                                    Select::make('cidade')
                                                        ->label('Cidade')
                                                        ->preload()
                                                        ->searchable()
                                                        ->options(function (Get $get) {
                                                            // Pega a sigla (valor) selecionada no campo 'estado'
                                                            $uf = $get('estado');
                                                            // Se o estado não estiver selecionado, não retorna nenhuma opção
                                                            if (empty($uf)) {
                                                                return [];
                                                            }
                                                            // Chama o novo método no seu serviço para buscar as cidades da UF
                                                            return IBGEServices::cidadesPorUf($uf);
                                                        })
                                                        ->columnSpan(3),
                                                ]),
                                            Section::make()
                                                ->description('Observações do cliente')
                                                ->icon('heroicon-s-chat-bubble-bottom-center-text')
                                                ->columns(1)
                                                ->schema([
                                                    Textarea::make('observacoes'),
                                                ])
                                        ])
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Selecione um cliente para prosseguir com o Aluguel.'
                                        ]),
                                ]),
                        ]),

                    Step::make('Carreta/Reboque')
                        ->schema([
                            Section::make()
                                ->description('Selecione a Carreta/Reboque para o aluguel')
                                ->icon('heroicon-s-document-text')
                                ->columns(3)
                                ->columnSpan(1)
                                ->schema([
                                    ModalTableSelect::make('carreta_id')
                                        ->relationship(
                                            'carreta',
                                            'identificacao'
                                        )
                                        ->label('Carreta/Reboque disponíveis')
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, callable $set, $state) {
                                            $carreta = Carreta::find($state);
                                            if ($carreta) {
                                                $valorFormatado = number_format((float) $carreta->valor_diaria, 2, ',', '.');
                                                $set('valor_diaria', $valorFormatado);
                                                $set('valor_total_aluguel', $valorFormatado);
                                                $set('valor_saldo_aluguel', $valorFormatado);

                                                $set('carreta.foto', $carreta->foto);
                                                $set('carreta.identificacao', $carreta->identificacao);
                                                $set('carreta.status', $carreta->status);
                                                $set('carreta.placa', $carreta->placa);
                                            }
                                        })
                                        ->tableConfiguration(CarretasTable::class)
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Selecione uma Carreta ou Reboque para prosseguir com o Aluguel.'
                                        ]),
                                    Section::make()
                                        ->columns(2)
                                        ->columnSpan('2')
                                        ->description('Detalhes da Carreta/Reboque')
                                        ->schema([
                                            TextEntry::make('carreta.identificacao')
                                                ->label('Nº de Identificação'),
                                            TextEntry::make('carreta.status')
                                                ->label('Status')
                                                ->badge()
                                                ->color(fn(string $state): string => match ($state) {
                                                    'disponivel' => 'success',
                                                    'em_manutencao' => 'warning',
                                                    'alugada' => 'danger',
                                                    default => 'secondary',
                                                }),
                                            TextEntry::make('carreta.placa')
                                                ->label('Placa')
                                                ->size(TextSize::Large)
                                                ->badge()
                                                ->color('secondary'),
                                            ImageEntry::make('carreta.foto')
                                                ->label('Imagem')
                                                ->disk('public'),
                                        ])
                                ]),
                        ]),

                    Step::make('Datas')
                        ->columns(3)
                        ->schema([

                            DateTimePicker::make('data_retirada')
                                ->label('Data de Retirada')
                                ->seconds(false)
                                ->default(now())
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    // Quando a data de retirada mudar, atualiza a previsão com +1 dia e +20min
                                    if ($state) {
                                        $dataRetirada = Carbon::parse($state);
                                        $dataPrevista = $dataRetirada->copy()->addDay()->addMinutes(20);
                                        $set('data_devolucao_prevista', $dataPrevista);
                                    }

                                    self::calcularTotais($set, $get);
                                })
                                ->required(),

                            DateTimePicker::make('data_devolucao_prevista')
                                ->label('Data de Prevista para Devolução')
                                ->seconds(false)
                                ->live()
                                ->default(function (Get $get) {
                                    // Define o padrão como data_retirada + 1 dia + 20 minutos
                                    $dataRetirada = $get('data_retirada');
                                    if ($dataRetirada) {
                                        return Carbon::parse($dataRetirada)->addDay()->addMinutes(20);
                                    }
                                    return now()->addDay()->addMinutes(20);
                                })
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calcularTotais($set, $get);
                                })
                                ->validationMessages([
                                    'after_or_equal' => 'A Data de Devolução Prevista deve ser igual ou posterior à Data de Retirada.',
                                    'required' => 'A Data de Devolução Prevista é obrigatória.',
                                ])
                                ->minDate(fn(Get $get) => $get('data_retirada'))
                                ->required(),

                            DateTimePicker::make('data_devolucao_real')
                                ->visible(fn(string $operation): bool => $operation === 'edit')
                                ->seconds(false)
                                ->label('Data da Devolução')
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calcularTotais($set, $get);
                                })
                                ->validationMessages([
                                    'after_or_equal' => 'A Data de Devolução deve ser igual ou posterior à Data de Retirada.',
                                    'required' => 'A Data de Devolução é obrigatória.',
                                ])
                                ->minDate(fn(Get $get) => $get('data_retirada')),
                        ]),

                    Step::make('Valores e Pagamento')
                        ->icon('heroicon-o-currency-dollar')
                        ->description('Defina os valores e registre os pagamentos')
                        ->columns(3)
                        ->schema([
                            Section::make('Resumo Financeiro')
                                ->columnSpan(1)
                                ->icon('heroicon-o-calculator')
                                ->schema([

                                    // Quantidade de Diárias (calculado automaticamente)
                                    TextInput::make('quantidade_diarias')
                                        ->label('Quantidade de Diárias')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('dia(s)')
                                        ->default(1)
                                        ->helperText('Calculado automaticamente pelas datas'),

                                    // Valor da Diária (editável)
                                    Money::make('valor_diaria')
                                        ->label('Valor da Diária')
                                        ->required()
                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                        ->helperText('Valor por dia de aluguel'),

                                    // Acréscimos
                                    Money::make('valor_acrescimo_aluguel')
                                        ->label('Acréscimos')
                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                        ->helperText('Taxas, multas, etc.'),

                                    // Descontos
                                    Money::make('valor_desconto_aluguel')
                                        ->label('Descontos')
                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                        ->helperText('Promoções, cortesias, etc.'),

                                    // Valor Total (readonly)
                                    Money::make('valor_total_aluguel')
                                        ->label('Valor Total')
                                        ->disabled()
                                        ->dehydrated()
                                        ->extraAttributes(['class' => 'font-bold text-lg'])
                                        ->helperText('Total do aluguel'),

                                    // Separador visual
                                    Section::make()
                                        ->schema([
                                            // Total Pago (calculado pelos movimentos)
                                            Money::make('valor_pago_aluguel')
                                                ->label('Total Pago')
                                                ->disabled()
                                                ->dehydrated()
                                                ->extraAttributes(['class' => 'text-green-600 font-semibold']),

                                            // Saldo Restante (calculado)
                                            Money::make('valor_saldo_aluguel')
                                                ->label('Saldo Restante')
                                                ->disabled()
                                                ->dehydrated()
                                                ->extraAttributes(['class' => 'text-red-600 font-bold text-lg']),
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Registrar Pagamentos')
                                ->columnSpan(2)
                                ->icon('heroicon-o-banknotes')
                                ->description('Adicione os pagamentos recebidos')
                                ->headerActions([
                                    // Você pode adicionar actions aqui se necessário
                                ])
                                ->schema([

                                    Repeater::make('movimentos')
                                        ->relationship()
                                        ->addActionLabel('Adicionar Pagamento')
                                        ->deletable(true)
                                        ->reorderable(false)
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            isset($state['valor_total_movimento'])
                                                ? 'Pagamento: R$ ' . number_format((float)$state['valor_total_movimento'], 2, ',', '.')
                                                : 'Novo Pagamento'
                                        )
                                        ->defaultItems(0)
                                        ->columns(4)
                                        ->schema([

                                            // Hidden: User ID
                                            Hidden::make('user_id')
                                                ->default(fn() => auth()->id()),

                                            // Hidden: Tipo (sempre entrada para aluguel)
                                            Hidden::make('tipo')
                                                ->default('entrada'),

                                            // Método de Pagamento
                                            ToggleButtons::make('metodo_pagamento_id')
                                                ->label('Forma de Pagamento')
                                                ->required()
                                                ->live()
                                                ->options(fn() => MetodoPagamento::pluck('nome', 'id'))
                                                ->icons([
                                                    1 => 'heroicon-o-banknotes',      // Dinheiro
                                                    2 => 'heroicon-o-credit-card',    // Cartão Crédito
                                                    3 => 'heroicon-o-credit-card',    // Cartão Débito
                                                    4 => 'heroicon-o-qr-code',        // PIX
                                                ])
                                                ->colors([
                                                    1 => 'success',
                                                    2 => 'info',
                                                    3 => 'warning',
                                                    4 => 'primary',
                                                ])
                                                ->inline()
                                                ->default(1)
                                                ->columnSpan(4),

                                            // Bandeira do Cartão (condicional)
                                            Select::make('cartao_pagamento_id')
                                                ->label('Bandeira do Cartão')
                                                ->relationship('bandeiraCartao', 'bandeira')
                                                ->searchable()
                                                ->preload()
                                                ->visible(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                ->required(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                ->columnSpan(4),

                                            // Número de Autorização (condicional)
                                            TextInput::make('autorizacao')
                                                ->label('Nº Autorização')
                                                ->placeholder('000000')
                                                ->maxLength(20)
                                                ->visible(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3, 4]))
                                                ->columnSpan(4),

                                            // Valor Pago pelo Cliente
                                            Money::make('valor_pago_movimento')
                                                ->label('Valor Pago')
                                                ->required()
                                                ->live(true)
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    $valorPago = self::normalizeMoney($state ?? 0);
                                                    $metodoPagamentoId = $get('metodo_pagamento_id');

                                                    // Buscar o método de pagamento
                                                    $metodo = MetodoPagamento::find($metodoPagamentoId);

                                                    // Valores atuais já existentes (normalizados)
                                                    $valorAcrescimoAtual = self::normalizeMoney($get('valor_acrescimo'));
                                                    $valorDescontoAtual  = self::normalizeMoney($get('valor_desconto'));

                                                    if ($metodo && $metodo->taxa_tipo !== 'N/A' && $metodo->taxa_percentual > 0) {

                                                        // Calcular taxa sobre o valor pago
                                                        $taxa = ($valorPago * $metodo->taxa_percentual) / 100;

                                                        if ($metodo->taxa_tipo === 'ACRESCENTAR') {

                                                            // Somar taxa ao valor já existente
                                                            $novoValorAcrescimo = $valorAcrescimoAtual + $taxa;

                                                            $set('valor_acrescimo', number_format($novoValorAcrescimo, 2, ',', '.'));
                                                            $set('valor_desconto', number_format($valorDescontoAtual, 2, ',', '.')); // mantém o existente

                                                        } elseif ($metodo->taxa_tipo === 'DESCONTAR') {

                                                            // Somar taxa ao valor já existente
                                                            $novoValorDesconto = $valorDescontoAtual + $taxa;

                                                            $set('valor_desconto', number_format($novoValorDesconto, 2, ',', '.'));
                                                            $set('valor_acrescimo', number_format($valorAcrescimoAtual, 2, ',', '.')); // mantém o existente
                                                        }
                                                    } else {
                                                        // Reset para 0 formatado
                                                        $set('valor_acrescimo', number_format(0, 2, ',', '.'));
                                                        $set('valor_desconto', number_format(0, 2, ',', '.'));
                                                    }


                                                    self::calcularTotalMovimento($set, $get);
                                                })
                                                ->helperText('Valor que será pago nesse pagamento')
                                                ->columnSpan(2),

                                            // Valor Recebido (para quando precisa dar troco)
                                            Money::make('valor_recebido_movimento')
                                                ->label('Valor Recebido')
                                                ->live(true)
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    $valorRecebido = self::normalizeMoney($state ?? 0);
                                                    $valorPago = self::normalizeMoney($get('valor_pago_movimento'));

                                                    if ($valorRecebido > $valorPago) {
                                                        $troco = $valorRecebido - $valorPago;
                                                        $set('troco_movimento', number_format($troco, 2, ',', '.'));
                                                    } else {
                                                        $set('troco_movimento', number_format(0, 2, ',', '.'));
                                                    }
                                                })
                                                ->helperText('Valor que está sendo entregue pelo cliente')
                                                ->columnSpan(2),


                                            // Troco
                                            Money::make('troco_movimento')
                                                ->label('Troco')
                                                ->disabled()
                                                ->dehydrated()
                                                ->extraAttributes(['class' => 'text-red-600 font-semibold'])
                                                ->helperText('Valor que será devolvido ao cliente')
                                                ->columnSpan(1),


                                            // Valor Total do Movimento
                                            Money::make('valor_total_movimento')
                                                ->label('Total')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated()
                                                ->extraAttributes(['class' => 'font-bold text-lg text-green-600'])
                                                ->columnSpan(2),
                                        ])
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            // Recalcular totais quando os movimentos mudarem
                                            self::atualizarTotaisPagamento($state, $set, $get);
                                        }),
                                ]),
                        ]),

                ]),
                Section::make()
                    ->description('Observações do Aluguel')
                    ->icon('heroicon-s-chat-bubble-bottom-center-text')
                    ->collapsed(fn(string $operation): bool => $operation === 'create')
                    ->schema([
                        Select::make('status')
                            ->hidden()
                            ->options([
                                'ativo' => 'Ativo',
                                'finalizado' => 'Finalizado',
                                'pendente' => 'Pendente',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('ativo')
                            ->required()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $dataDevolucao = $get('data_devolucao_real');
                                $saldo = floatval($get('valor_saldo_aluguel') ?? 0);

                                if ($dataDevolucao && $saldo == 0) {
                                    $set('status', 'finalizado');
                                } elseif ($dataDevolucao && $saldo > 0) {
                                    $set('status', 'pendente');
                                } else {
                                    $set('status', 'ativo');
                                }
                            })
                            ->dehydrateStateUsing(function (Get $get) {
                                $dataDevolucao = $get('data_devolucao_real');
                                $saldo = floatval($get('valor_saldo_aluguel') ?? 0);

                                if ($dataDevolucao && $saldo == 0) {
                                    return 'finalizado';
                                }

                                if ($dataDevolucao && $saldo > 0) {
                                    return 'pendente';
                                }

                                return 'ativo';
                            }),

                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ])

            ]);
    }

    protected static function normalizeMoney($value): float
    {
        if (is_null($value) || $value === '') {
            return 0;
        }

        // Remove pontos de milhar
        $value = str_replace('.', '', $value);

        // Troca vírgula decimal por ponto
        $value = str_replace(',', '.', $value);

        return floatval($value);
    }


    /**
     * Calulura totais
     */
    protected static function calcularTotais(Set $set, Get $get)
    {
        $dataRetirada = $get('data_retirada');
        $valorDiaria = floatval($get('valor_diaria'));

        $dataDevolucaoReal = $get('data_devolucao_real');
        $dataDevolucaoPrevista = $get('data_devolucao_prevista');

        // Prioriza devolução real
        if ($dataDevolucaoReal) {
            $dataFim = Carbon::parse($dataDevolucaoReal);
        } elseif ($dataDevolucaoPrevista) {
            $dataFim = Carbon::parse($dataDevolucaoPrevista);
        } else {
            $dataFim = null;
        }

        if ($dataRetirada && $dataFim) {

            $inicio = Carbon::parse($dataRetirada);

            // Diferença TOTAL em minutos
            $minutos = $inicio->diffInMinutes($dataFim);

            // 1 diária = 1440 minutos (24h)
            $minutosPorDiaria = 1440;

            // Calcula quantas diárias completas
            $dias = intdiv($minutos, $minutosPorDiaria);

            // Verifica minutos restantes
            $resto = $minutos % $minutosPorDiaria;

            // Se o resto > 20 minutos → cobra mais 1 diária
            if ($resto > 20) {
                $dias++;
            }

            // Garante no mínimo 1 diária
            if ($dias <= 0) $dias = 1;

            // Atualiza campo quantidade_diarias
            $set('quantidade_diarias', $dias);

            // Calcula valor total
            $valorTotal = $valorDiaria * $dias;

            $set('valor_total_aluguel', number_format($valorTotal, 2, ',', '.'));
        } else {
            $set('quantidade_diarias', null);
            $set('valor_total_aluguel', "0,00");
        }

        // Atualizar totais pagamento
        self::atualizarTotaisPagamento(
            $get('movimentos'),
            $set,
            $get
        );
    }

    /**
     * Calcula os valores totais do aluguel
     */
    protected static function calcularValores(Set $set, Get $get): void
    {
        $valorDiaria       = self::normalizeMoney($get('valor_diaria'));
        $quantidadeDiarias = intval($get('quantidade_diarias') ?? 1);
        $valorAcrescimo    = self::normalizeMoney($get('valor_acrescimo_aluguel'));
        $valorDesconto     = self::normalizeMoney($get('valor_desconto_aluguel'));

        // Calcular subtotal
        $subtotal = $valorDiaria * $quantidadeDiarias;

        // Calcular total
        $valorTotal = $subtotal + $valorAcrescimo - $valorDesconto;

        $set('valor_total_aluguel', number_format($valorTotal, 2, ',', '.'));

        self::atualizarTotaisPagamento($get('movimentos'), $set, $get);
    }

    /**
     * Calcula o total de um movimento específico
     */
    protected static function calcularTotalMovimento(Set $set, Get $get): void
    {
        $valorPago      = self::normalizeMoney($get('valor_pago_movimento'));
        $valorAcrescimo = self::normalizeMoney($get('valor_acrescimo_movimento'));
        $valorDesconto  = self::normalizeMoney($get('valor_desconto_movimento'));

        $valorTotal = $valorPago + $valorAcrescimo - $valorDesconto;

        $set('valor_total_movimento', number_format($valorTotal, 2, ',', '.'));
    }


    /**
     * Atualiza os totais de pagamento no resumo
     */
    protected static function atualizarTotaisPagamento(array $movimentos, Set $set, Get $get): void
    {
        $totalPago = 0;

        // 1. Calcular o total pago pelos movimentos
        if (is_array($movimentos)) {
            foreach ($movimentos as $movimento) {
                if (isset($movimento['valor_total_movimento'])) {
                    $totalPago += self::normalizeMoney($movimento['valor_total_movimento']);
                }
            }
        }

        // 2. Total do aluguel corretamente normalizado (aceita vírgula e ponto)
        $valorTotalAluguel = self::normalizeMoney($get('valor_total_aluguel'));

        // 3. Calcular saldo
        $saldo = $valorTotalAluguel - $totalPago;

        // 4. Definir valores formatados para exibição no resumo
        $set('valor_pago_aluguel', number_format($totalPago, 2, ',', '.'));
        $set('valor_saldo_aluguel', number_format($saldo, 2, ',', '.'));
    }
}
