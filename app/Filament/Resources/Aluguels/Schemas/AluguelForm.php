<?php

namespace App\Filament\Resources\Aluguels\Schemas;

use App\Filament\Tables\CarretasTable;
use App\Models\Carreta;
use App\Models\MetodoPagamento;
use App\Services\IBGEServices;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

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
                                        ->relationship('cliente', 'nome')
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
                                                                ->mask(RawJs::make(<<<'JS'
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
                                ->columns(2)
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
                                                $set('carreta.foto', $carreta->foto);
                                                $set('carreta.identificacao', $carreta->identificacao);
                                                $set('carreta.status', $carreta->status);
                                            }
                                        })
                                        ->tableConfiguration(CarretasTable::class)
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Selecione uma Carreta ou Reboque para prosseguir com o Aluguel.'
                                        ]),
                                    Section::make()
                                        ->description('Detalhes da Carreta/Reboque')
                                        ->schema([
                                            TextEntry::make('carreta.identificacao')
                                                ->label('Nº de Identificação'),
                                            TextEntry::make('carreta.status')
                                                ->label('Status da Carreta/Reboque')
                                                ->badge()
                                                ->color(fn(string $state): string => match ($state) {
                                                    'disponivel' => 'success',
                                                    'em_manutencao' => 'warning',
                                                    'alugada' => 'danger',
                                                    default => 'secondary',
                                                }),
                                            ImageEntry::make('carreta.foto')
                                                ->label('Imagem da Carreta/Reboque')
                                                ->disk('public'),
                                        ])
                                ]),
                        ]),
                    Step::make('Datas')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('data_retirada')
                                ->label('Data de Retirada')
                                ->default(now())
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $dataDevolucao = $get('data_devolucao_prevista');
                                    $valorDiaria = floatval($get('valor_diaria') ?? 0);

                                    if ($dataDevolucao && $state) {
                                        $inicio = Carbon::parse($state);
                                        $fim = Carbon::parse($dataDevolucao);

                                        // diferença em dias (se for 0 ou negativo, considera 1 diária)
                                        $dias = $inicio->diffInDays($fim);
                                        $diasValidos = $dias > 0 ? $dias : 1;

                                        $set('quantidade_diarias', $diasValidos);

                                        // 1. Calcule o valor total
                                        $valorTotal = $valorDiaria * $diasValidos;

                                        // 2. Formate o valor para string com separadores brasileiros
                                        $valorFormatado = number_format((float) $valorTotal, 2, ',', '.'); // Ex: '80.000,00'
                        
                                        // 3. Defina o estado com a string formatada
                                        $set('valor_total_aluguel', $valorFormatado);
                                    } else {
                                        // se algum dos dois campos estiver vazio, zera quantidade/total (opcional)
                                        $set('quantidade_diarias', null);
                                        $set('valor_total_aluguel', '0,00');
                                    }
                                })
                                ->required(),
                            DatePicker::make('data_devolucao_prevista')
                                ->label('Data de Prevista para Devolução')
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $dataRetirada = $get('data_retirada');
                                    $valorDiaria = floatval($get('valor_diaria') ?? 0);

                                    if ($dataRetirada && $state) {
                                        $inicio = Carbon::parse($dataRetirada);
                                        $fim = Carbon::parse($state);

                                        // diferença em dias (se for 0 ou negativo, considera 1 diária)
                                        $dias = $inicio->diffInDays($fim);
                                        $diasValidos = $dias > 0 ? $dias : 1;

                                        $set('quantidade_diarias', $diasValidos);

                                        // 1. Calcule o valor total
                                        $valorTotal = $valorDiaria * $diasValidos;

                                        // 2. Formate o valor para string com separadores brasileiros
                                        $valorFormatado = number_format((float) $valorTotal, 2, ',', '.'); // Ex: '80.000,00'
                        
                                        // 3. Defina o estado com a string formatada
                                        $set('valor_total_aluguel', $valorFormatado);
                                    } else {
                                        // se algum dos dois campos estiver vazio, zera quantidade/total (opcional)
                                        $set('quantidade_diarias', null);
                                        $set('valor_total_aluguel', '0,00');
                                    }
                                })
                                ->validationMessages([
                                    'after_or_equal' => 'A Data de Devolução Prevista deve ser igual ou posterior à Data de Retirada.',
                                    'required' => 'A Data de Devolução Prevista é obrigatória.',
                                ])
                                ->minDate(fn (Get $get) => $get('data_retirada'))
                                ->required(),
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
                                        TextInput::make('valor_diaria')
                                            ->label('Valor da Diária')
                                            ->mask(RawJs::make(<<<'JS'
                                                $money($input, ',', '.', 2)
                                            JS))
                                            ->dehydrateStateUsing(function ($state) {
                                                // Remove formatação antes de salvar
                                                if (!$state)
                                                    return 0;

                                                // Remove R$, pontos e converte vírgula em ponto
                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                $value = str_replace(',', '.', $value);

                                                return (float) $value;
                                            })
                                            ->formatStateUsing(function ($state) {
                                                // Formata para exibição
                                                if (!$state)
                                                    return '0,00';

                                                return number_format((float) $state, 2, ',', '.');
                                            })
                                            ->required()
                                            ->live(true)
                                            ->prefix('R$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->afterStateUpdated(fn ($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Valor por dia de aluguel'),
                                        
                                        // Acréscimos
                                        TextInput::make('valor_acrescimo_aluguel')
                                            ->label('Acréscimos')
                                            ->mask(RawJs::make(<<<'JS'
                                                $money($input, ',', '.', 2)
                                            JS))
                                            ->dehydrateStateUsing(function ($state) {
                                                // Remove formatação antes de salvar
                                                if (!$state)
                                                    return 0;

                                                // Remove R$, pontos e converte vírgula em ponto
                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                $value = str_replace(',', '.', $value);

                                                return (float) $value;
                                            })
                                            ->formatStateUsing(function ($state) {
                                                // Formata para exibição
                                                if (!$state)
                                                    return '0,00';

                                                return number_format((float) $state, 2, ',', '.');
                                            })
                                            ->placeholder('0,00')
                                            ->prefix('R$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(fn ($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Taxas, multas, etc.'),
                                        
                                        // Descontos
                                        TextInput::make('valor_desconto_aluguel')
                                            ->label('Descontos')
                                            ->mask(RawJs::make(<<<'JS'
                                                    $money($input, ',', '.', 2)
                                                JS))
                                            ->dehydrateStateUsing(function ($state) {
                                                // Remove formatação antes de salvar
                                                if (!$state)
                                                    return 0;

                                                // Remove R$, pontos e converte vírgula em ponto
                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                $value = str_replace(',', '.', $value);

                                                return (float) $value;
                                            })
                                            ->formatStateUsing(function ($state) {
                                                // Formata para exibição
                                                if (!$state)
                                                    return '0,00';

                                                return number_format((float) $state, 2, ',', '.');
                                            })
                                            ->placeholder('0,00')
                                            ->prefix('R$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(fn ($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Promoções, cortesias, etc.'),
                                        
                                        // Valor Total (readonly)
                                        TextInput::make('valor_total_aluguel')
                                            ->label('Valor Total')
                                            ->required()
                                            ->prefix('R$')
                                            ->disabled()
                                            ->dehydrated()
                                            ->dehydrateStateUsing(function ($state) {
                                                // Remove formatação antes de salvar
                                                if (!$state)
                                                    return 0;

                                                // Remove R$, pontos e converte vírgula em ponto
                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                $value = str_replace(',', '.', $value);

                                                return (float) $value;
                                            })
                                            ->extraAttributes(['class' => 'font-bold text-lg'])
                                            ->helperText('Total do aluguel'),
                                        
                                        // Separador visual
                                        Section::make()
                                            ->schema([
                                                // Total Pago (calculado pelos movimentos)
                                                TextInput::make('valor_pago_aluguel')
                                                    ->label('Total Pago')
                                                    ->prefix('R$')
                                                    ->dehydrateStateUsing(function ($state) {
                                                        // Remove formatação antes de salvar
                                                        if (!$state)
                                                            return 0;

                                                        // Remove R$, pontos e converte vírgula em ponto
                                                        $value = str_replace(['R$', '.', ' '], '', $state);
                                                        $value = str_replace(',', '.', $value);

                                                        return (float) $value;
                                                    })
                                                    ->default('0,00')
                                                    ->extraAttributes(['class' => 'text-green-600 font-semibold']),
                                                
                                                // Saldo Restante (calculado)
                                                TextInput::make('valor_saldo_aluguel')
                                                    ->label('Saldo Restante')
                                                    ->prefix('R$')
                                                    ->dehydrateStateUsing(function ($state) {
                                                        // Remove formatação antes de salvar
                                                        if (!$state)
                                                            return 0;

                                                        // Remove R$, pontos e converte vírgula em ponto
                                                        $value = str_replace(['R$', '.', ' '], '', $state);
                                                        $value = str_replace(',', '.', $value);

                                                        return (float) $value;
                                                    })
                                                    ->default('0,00')
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
                                            ->label('')
                                            ->addActionLabel('Adicionar Pagamento')
                                            ->deletable(true)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(fn (array $state): ?string => 
                                                isset($state['valor_total_movimento']) 
                                                    ? 'Pagamento: R$ ' . number_format((float)$state['valor_total_movimento'], 2, ',', '.')
                                                    : 'Novo Pagamento'
                                            )
                                            ->defaultItems(1)
                                            ->columns(4)
                                            ->schema([
                                                        
                                                        // Descrição
                                                        TextInput::make('descricao')
                                                            ->label('Descrição')
                                                            ->placeholder('Ex: Pagamento inicial, Parcela 1/3, etc.')
                                                            ->default('Pagamento')
                                                            ->columnSpan(4),
                                                        
                                                        // Hidden: User ID
                                                        Hidden::make('user_id')
                                                            ->default(fn () => auth()->id()),
                                                        
                                                        // Hidden: Tipo (sempre entrada para aluguel)
                                                        Hidden::make('tipo')
                                                            ->default('entrada'),
                                                        
                                                        // Método de Pagamento
                                                        ToggleButtons::make('metodo_pagamento_id')
                                                            ->label('Forma de Pagamento')
                                                            ->required()
                                                            ->live()
                                                            ->options(fn () => MetodoPagamento::pluck('nome', 'id'))
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
                                                            ->visible(fn (Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                            ->required(fn (Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                            ->columnSpan(2),
                                                        
                                                        // Número de Autorização (condicional)
                                                        TextInput::make('autorizacao')
                                                            ->label('Nº Autorização')
                                                            ->placeholder('000000')
                                                            ->maxLength(20)
                                                            ->visible(fn (Get $get) => in_array($get('metodo_pagamento_id'), [2, 3, 4]))
                                                            ->columnSpan(2),
                                                        
                                                        // Valor Pago pelo Cliente
                                                        TextInput::make('valor_pago_movimento')
                                                            ->label('Valor Pago')
                                                            ->required()
                                                            ->prefix('R$')
                                                            ->minValue(0)
                                                            ->step(0.01)
                                                           ->mask(RawJs::make(<<<'JS'
                                                                $money($input, ',', '.', 2)
                                                            JS))
                                                            ->dehydrateStateUsing(function ($state) {
                                                                // Remove formatação antes de salvar
                                                                if (!$state)
                                                                    return 0;

                                                                // Remove R$, pontos e converte vírgula em ponto
                                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                                $value = str_replace(',', '.', $value);

                                                                return (float) $value;
                                                            })
                                                            ->formatStateUsing(function ($state) {
                                                                // Formata para exibição
                                                                if (!$state)
                                                                    return '0,00';

                                                                return number_format((float) $state, 2, ',', '.');
                                                            })
                                                            ->placeholder('0,00')
                                                            ->live(true)
                                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                $valorPago = floatval($state ?? 0);
                                                                $metodoPagamentoId = $get('metodo_pagamento_id');
                                                                
                                                                // Buscar taxa do método de pagamento
                                                                $metodo = MetodoPagamento::find($metodoPagamentoId);
                                                                
                                                                if ($metodo && $metodo->taxa_tipo !== 'N/A' && $metodo->taxa_percentual > 0) {
                                                                    $taxa = ($valorPago * $metodo->taxa_percentual) / 100;
                                                                    
                                                                    if ($metodo->taxa_tipo === 'ACRESCENTAR') {
                                                                        $set('valor_acrescimo', $taxa);
                                                                        $set('valor_desconto', 0);
                                                                    } elseif ($metodo->taxa_tipo === 'DESCONTAR') {
                                                                        $set('valor_desconto', $taxa);
                                                                        $set('valor_acrescimo', 0);
                                                                    }
                                                                } else {
                                                                    $set('valor_acrescimo', 0);
                                                                    $set('valor_desconto', 0);
                                                                }
                                                                
                                                                self::calcularTotalMovimento($set, $get);
                                                            })
                                                            ->helperText('Valor que será pago nesse pagamento')
                                                            ->columnSpan(2),
                                                        
                                                        // Valor Recebido (para quando precisa dar troco)
                                                        TextInput::make('valor_recebido_movimento')
                                                            ->label('Valor Recebido')
                                                            ->prefix('R$')
                                                            ->minValue(0)
                                                            ->step(0.01)
                                                            ->mask(RawJs::make(<<<'JS'
                                                                $money($input, ',', '.', 2)
                                                            JS))
                                                            ->dehydrateStateUsing(function ($state) {
                                                                // Remove formatação antes de salvar
                                                                if (!$state)
                                                                    return 0;

                                                                // Remove R$, pontos e converte vírgula em ponto
                                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                                $value = str_replace(',', '.', $value);

                                                                return (float) $value;
                                                            })
                                                            ->formatStateUsing(function ($state) {
                                                                // Formata para exibição
                                                                if (!$state)
                                                                    return '0,00';

                                                                return number_format((float) $state, 2, ',', '.');
                                                            })
                                                            ->placeholder('0,00')
                                                            ->live(true)
                                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                $valorRecebido = floatval($state ?? 0);
                                                                $valorPago = floatval($get('valor_pago_movimento') ?? 0);
                                                                
                                                                if ($valorRecebido > $valorPago) {
                                                                    $troco = $valorRecebido - $valorPago;
                                                                    $set('troco_movimento', $troco);
                                                                } else {
                                                                    $set('troco_movimento', 0);
                                                                }
                                                            })
                                                            //->visible(fn (Get $get) => $get('metodo_pagamento_id') == 1) // Só para dinheiro
                                                            ->helperText('Valor que está sendo entregue pelo cliente')
                                                            ->columnSpan(2),
                                                        
                                                        // Troco
                                                        TextInput::make('troco_movimento')
                                                            ->label('Troco')
                                                            ->numeric()
                                                            ->prefix('R$')
                                                            ->readOnly()
                                                            ->default(0)
                                                            //->visible(fn (Get $get) => $get('metodo_pagamento_id') == 1)
                                                            ->extraAttributes(['class' => 'text-red-600 font-semibold'])
                                                            ->helperText('Valor que será devolvido ao cliente')
                                                            ->columnSpan(1),
                                                        
                                                        // Acréscimo (taxa)
                                                        TextInput::make('valor_acrescimo_movimento')
                                                            ->label('Acréscimo')
                                                            ->numeric()
                                                            ->prefix('R$')
                                                            ->readOnly()
                                                            ->default(0)
                                                            ->visible(fn (Get $get) => floatval($get('valor_acrescimo') ?? 0) > 0)
                                                            ->helperText('Taxa do método de pagamento')
                                                            ->columnSpan(1),
                                                        
                                                        // Desconto (taxa)
                                                        TextInput::make('valor_desconto_movimento')
                                                            ->label('Desconto')
                                                            ->numeric()
                                                            ->prefix('R$')
                                                            ->readOnly()
                                                            ->default(0)
                                                            ->visible(fn (Get $get) => floatval($get('valor_desconto') ?? 0) > 0)
                                                            ->helperText('Taxa do método de pagamento')
                                                            ->columnSpan(1),
                                                        
                                                        // Valor Total do Movimento
                                                        TextInput::make('valor_total_movimento')
                                                            ->label('Total')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('R$')
                                                            ->readOnly()
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
                            ->options(['ativo' => 'Ativo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
                            ->default('ativo')
                            ->required(),
                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ])

            ]);
    }
    /**
     * Calcula os valores totais do aluguel
     */
    protected static function calcularValores(Set $set, Get $get): void
    {
        dd($get('valor_diaria'));
        $valorDiaria = floatval($get('valor_diaria') ?? 0);
        $quantidadeDiarias = intval($get('quantidade_diarias') ?? 1);
        $valorAcrescimo = floatval($get('valor_acrescimo_aluguel') ?? 0);
        $valorDesconto = floatval($get('valor_desconto_aluguel') ?? 0);

        // Calcular subtotal
        $subtotal = $valorDiaria * $quantidadeDiarias;

        // Calcular total
        $valorTotal = $subtotal + $valorAcrescimo - $valorDesconto;

        $set('valor_total_aluguel', $valorTotal);
    }

    /**
     * Calcula o total de um movimento específico
     */
    protected static function calcularTotalMovimento(Set $set, Get $get): void
    {
        $valorPago = floatval($get('valor_pago_movimento') ?? 0);
        $valorAcrescimo = floatval($get('valor_acrescimo_movimento') ?? 0);
        $valorDesconto = floatval($get('valor_desconto_movimento') ?? 0);

        $valorTotal = $valorPago + $valorAcrescimo - $valorDesconto;

        $set('valor_total_movimento', $valorTotal);
    }

    /**
     * Atualiza os totais de pagamento no resumo
     */
    protected static function atualizarTotaisPagamento(array $movimentos, Set $set, Get $get): void
    {
        $totalPago = 0;

        if (is_array($movimentos)) {
            foreach ($movimentos as $movimento) {
                if (isset($movimento['valor_total_movimento'])) {
                    $totalPago += floatval($movimento['valor_total_movimento']);
                }
            }
        }

        $valorTotal = floatval($get('valor_total_aluguel') ?? 0);
        $saldo = max(0, $valorTotal - $totalPago);

        $set('valor_pago_aluguel', number_format($totalPago, 2, ',', '.'));
        $set('valor_saldo_aluguel', number_format($saldo, 2, ',', '.'));
    }
}
