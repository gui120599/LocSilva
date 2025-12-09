<?php

namespace App\Filament\Resources\Aluguels\Tables;

use App\Helper\FormatHelper;
use App\Models\Aluguel;
use App\Models\BandeiraCartaoPagamento;
use App\Models\Carreta;
use App\Models\MetodoPagamento;
use Carbon\Carbon;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Support\RawJs;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Leandrocfe\FilamentPtbrFormFields\Currencies\BRL;
use Leandrocfe\FilamentPtbrFormFields\Money;
use Filament\Schemas\Components\Wizard;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rules\Date;
use Leandrocfe\FilamentPtbrFormFields\Document;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;
use Leandrocfe\FilamentPtbrFormFields\PtbrCpfCnpj;

class AluguelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('status')
                    ->tooltip(fn(string $state): string => match ($state) {
                        'ativo' => 'ATIVO',
                        'finalizado' => 'FINALIZADO',
                        'pendente' => 'PENDENTE',
                        'cancelado' => 'CANCELADO',
                        default => strtoupper($state),
                    })
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'ativo' => Heroicon::OutlinedTruck,
                        'finalizado' => Heroicon::OutlinedCheckCircle,
                        'pendente' => Heroicon::OutlinedExclamationCircle,
                        'cancelado' => Heroicon::OutlinedXCircle,
                    })
                    ->colors([
                        'success' => 'ativo',
                        'info' => 'finalizado',
                        'warning' => 'pendente',
                        'danger' => 'cancelado',
                    ]),
                TextColumn::make('cliente.nome')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cliente.cpf_cnpj')
                    ->label('CPF/CNPJ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => FormatHelper::formatCpfCnpj($state)),

                TextColumn::make('cliente.telefone')
                    ->label('Telefone')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => FormatHelper::formatTelefone($state)),

                TextColumn::make('carreta.identificacao')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_retirada')
                    ->label('Data Retirada')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_devolucao_prevista')
                    ->label('Devolução Prevista')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_devolucao_real')
                    ->label('Devolução Real')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('quantidade_diarias')
                    ->label('Diárias')
                    ->description('Dias')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valor_diaria')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valor_total_aluguel')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valor_pago_aluguel')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valor_saldo_aluguel')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Deletado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('status')
                    ->multiple()
                    ->label('Status')
                    ->options([
                        'ativo' => 'Ativo',
                        'finalizado' => 'Finalizado',
                        'pendente' => 'Pendente',
                        'cancelado' => 'Cancelado',
                    ]),

                Filter::make('diarias')
                    ->schema([
                        TextInput::make('quantidade_diarias')
                            ->label('QTD de Diárias')
                            ->placeholder('Digite a quantidade de diárias'),
                    ]),

                Filter::make('data_retirada')
                    ->label('Data de Retirada')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        // Somente data
                        DatePicker::make('data_retirada_de')
                            ->label('Data de Retirada (De)'),

                        // Somente data
                        DatePicker::make('data_retirada_ate')
                            ->label('Data de Retirada (Até)'),

                        // Com hora
                        /*DateTimePicker::make('data_retirada_datetime_de')
                            ->label('Data/Hora de Retirada (De)'),

                        // Com hora
                        DateTimePicker::make('data_retirada_datetime_ate')
                            ->label('Data/Hora de Retirada (Até)'),*/
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query

                            // 🔹 FILTRO POR DATA (somente data)
                            ->when($data['data_retirada_de'] ?? null, function ($query, $date) {
                                $query->whereDate('data_retirada', '>=', $date);
                            })
                            ->when($data['data_retirada_ate'] ?? null, function ($query, $date) {
                                $query->whereDate('data_retirada', '<=', $date);
                            });

                            // 🔹 FILTRO POR DATETIME (com hora)
                           /* ->when($data['data_retirada_datetime_de'] ?? null, function ($query, $dateTime) {
                                $query->where('data_retirada', '>=', $dateTime);
                            })
                            ->when($data['data_retirada_datetime_ate'] ?? null, function ($query, $dateTime) {
                                $query->where('data_retirada', '<=', $dateTime);
                            });*/
                    }),



                Filter::make('data_devolucao_real')
                    ->label('Data de Devolução Real')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        // Somente data
                        DatePicker::make('data_devolucao_real_de')
                            ->label('Devolução Real (De)'),

                        // Somente data
                        DatePicker::make('data_devolucao_real_ate')
                            ->label('Devolução Real (Até)'),

                        // Com hora
                       /* DateTimePicker::make('data_devolucao_real_datetime_de')
                            ->label('Data/Hora de Devolução (De)'),

                        // Com hora
                        DateTimePicker::make('data_devolucao_real_datetime_ate')
                            ->label('Data/Hora de Devolução (Até)'),*/
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query

                            // 🔹 FILTRO POR DATA
                            ->when($data['data_devolucao_real_de'] ?? null, function ($query, $date) {
                                $query->whereDate('data_devolucao_real', '>=', $date);
                            })
                            ->when($data['data_devolucao_real_ate'] ?? null, function ($query, $date) {
                                // acrescenta 23:59:59 para não cortar registros do final do dia
                                $query->where('data_devolucao_real', '<=', $date . ' 23:59:59');
                            });

                            // 🔹 FILTRO POR DATETIME
                           /* ->when($data['data_devolucao_real_datetime_de'] ?? null, function ($query, $dateTime) {
                                $query->where('data_devolucao_real', '>=', $dateTime);
                            })
                            ->when($data['data_devolucao_real_datetime_ate'] ?? null, function ($query, $dateTime) {
                                $query->where('data_devolucao_real', '<=', $dateTime);
                            });*/
                    }),

                Filter::make('id_aluguel')
                    ->label('ID do aluguel')
                    ->schema([
                        TextInput::make('id_aluguel')
                            ->label('ID do Aluguel')
                            ->placeholder('Digite o ID do Aluguel'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['id_aluguel'] ?? null, function (Builder $query, $idAluguel) {
                            $query->where('id', $idAluguel);
                        });
                    }),

                Filter::make('cliente')
                    ->label('Cliente')
                    ->schema([
                        Grid::make()
                            ->columns(6)
                            ->components([
                                TextInput::make('id_cliente')
                                    ->label('ID do Cliente')
                                    ->placeholder('Digite o id do cliente')
                                    ->columnSpan(1),

                                TextInput::make('cliente_nome')
                                    ->label('Nome do Cliente')
                                    ->placeholder('Digite o nome do cliente')
                                    ->columnSpan(3),

                                Document::make('cliente_cpf_cnpj')
                                    ->label('CPF/CNPJ')
                                    ->dynamic()
                                    ->columnSpan(2)
                                    ->placeholder('Digite o CPF ou CNPJ do cliente'),

                                PhoneNumber::make('telefone')
                                    ->label('Telefone')
                                    ->columnSpan(3)
                                    ->placeholder('(99) 99999-9999')
                                    ->mask('(99) 9999-9999'),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['cliente_nome'] ?? null, function (Builder $query, $clienteNome) {
                            $query->whereHas('cliente', function (Builder $query) use ($clienteNome) {
                                $query->where('nome', 'like', '%' . $clienteNome . '%');
                            });
                        })->when(preg_replace("/\D/", "", $data['cliente_cpf_cnpj']) ?? null, function (Builder $query, $clienteCpfCnpj) {
                            $query->whereHas('cliente', function (Builder $query) use ($clienteCpfCnpj) {
                                $query->where('cpf_cnpj', 'like', '%' . $clienteCpfCnpj . '%');
                            });
                        })->when($data['id_cliente'] ?? null, function (Builder $query, $idCliente) {
                            $query->whereHas('cliente', function (Builder $query) use ($idCliente) {
                                $query->where('id', $idCliente);
                            });
                        })->when(preg_replace("/\D/", "", $data['telefone']) ?? null, function (Builder $query, $clienteTelefone) {
                            $query->whereHas('cliente', function (Builder $query) use ($clienteTelefone) {
                                $query->where('telefone', 'like', '%' . $clienteTelefone . '%');
                            });
                        });
                    }),

                Filter::make('carreta')
                    ->schema([
                        TextInput::make('carreta_identificacao')
                            ->label('Nº de Identificação')
                            ->placeholder('Digite o número de identificação da carreta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['carreta_identificacao'] ?? null, function (Builder $query, $carretaIdentificacao) {
                                $query->whereHas('carreta', function (Builder $query) use ($carretaIdentificacao) {
                                    $query->where('identificacao', $carretaIdentificacao);
                                });
                            });
                    }),





            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->filtersFormSchema(fn(array $filters): array => [
                Section::make('Aluguel')
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        $filters['id_aluguel'] ?? null,
                        $filters['diarias'] ?? null,
                        $filters['status'] ?? null,
                        $filters['data_retirada'] ?? null,
                        $filters['data_devolucao_real'] ?? null,
                    ]),
                Section::make('Cliente')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        $filters['cliente'] ?? null,
                    ]),

                Section::make('Carreta')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        $filters['carreta'] ?? null,
                    ]),
            ])
            ->deferFilters(false)
            ->recordActions([

                Action::make('print')
                    ->label('Retirada')
                    ->icon('heroicon-s-printer')
                    ->color('primary')
                    // A CHAVE: Passar o registro ($record) para a rota dentro do closure
                    ->url(fn($record): string => route('print-retirada', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => in_array($record->status, ['ativo', 'pendente'])),

                /*Action::make('print')
                    ->label('devolucao')
                    ->icon('heroicon-s-printer')
                    ->color('primary')
                    // A CHAVE: Passar o registro ($record) para a rota dentro do closure
                    ->url(fn($record): string => route('print-devolucao', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => in_array($record->status, ['ativo'])),*/

                Action::make('Recibo')
                    ->url(fn($record) => \App\Filament\Resources\Aluguels\AluguelResource::getUrl('aluguel', ['record' => $record])),

                Action::make('finalizar')
                    ->label('Finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->status, ['ativo', 'pendente']))
                    ->requiresConfirmation()
                    ->modalHeading('Finalizar Aluguel')
                    ->modalDescription(function (Aluguel $record): string {
                        $saldoRestante = $record->valor_total_aluguel - $record->movimentos->sum('valor_total_movimento');

                        if ($saldoRestante > 0.01) {
                            $saldoFormatado = number_format($saldoRestante, 2, ',', '.');
                            return "Informe a data real de devolução e acerte o saldo pendente de R$ {$record->valor_saldo_aluguel}.";
                        }

                        return 'Informe a data real de devolução.';
                    })
                    ->fillForm(function ($record): array {
                        return [
                            'data_devolucao_real' => $record->data_devolucao_real,
                            'quantidade_diarias' => $record->quantidade_diarias,
                            'valor_diaria' => $record->valor_diaria,
                            'valor_acrescimo_aluguel' => $record->valor_acrescimo_aluguel,
                            'valor_desconto_aluguel' => $record->valor_desconto_aluguel,
                            'valor_total_aluguel' => $record->valor_total_aluguel,
                            'valor_pago_aluguel' => number_format($record->movimentos->sum('valor_total_movimento'), '2', ',', '.'),
                            'valor_saldo_aluguel' => number_format(max(0, $record->valor_total_aluguel - $record->movimentos->sum('valor_total_movimento')), '2', ',', '.'),
                            'movimentos_existentes' => $record->movimentos->map(function ($movimento) {
                                return [
                                    'descricao' => $movimento->descricao,
                                    'user_id' => $movimento->user_id,
                                    'tipo' => $movimento->tipo,
                                    'metodo_pagamento_id' => $movimento->metodo_pagamento_id,
                                    'cartao_pagamento_id' => $movimento->cartao_pagamento_id,
                                    'autorizacao' => $movimento->autorizacao,
                                    'valor_pago_movimento' => $movimento->valor_pago_movimento,
                                    'valor_recebido_movimento' => $movimento->valor_recebido_movimento,
                                    'troco_movimento' => $movimento->troco_movimento,
                                    'valor_total_movimento' => $movimento->valor_total_movimento,
                                ];
                            })->toArray(),
                        ];
                    })
                    ->form(fn(Aluguel $record): array => [
                        Wizard::make([
                            Step::make('Finalizar Aluguel')
                                ->description('Confirme os detalhes para finalizar o aluguel')
                                ->schema([
                                    DateTimePicker::make('data_devolucao_real')
                                        ->disabled(fn($record) => in_array($record->status, ['pendente']))
                                        ->default($record->data_devolucao_real)
                                        ->dehydrated()
                                        ->label('Data de Devolução')
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) use ($record) {

                                            $dataRetirada = $record->data_retirada;
                                            $valorDiaria = floatval($record->valor_diaria ?? 0);
                                            $valorAcrescimo = self::normalizeMoney($get('valor_acrescimo_aluguel') ?? 0);
                                            $valorDesconto = self::normalizeMoney($get('valor_desconto_aluguel') ?? 0);

                                            if ($dataRetirada && $state) {

                                                $inicio = Carbon::parse($dataRetirada);
                                                $fim = Carbon::parse($state);

                                                // Diferença total em minutos
                                                $minutos = $inicio->diffInMinutes($fim);

                                                // 1 diária = 1440 minutos (24h)
                                                $minutosPorDiaria = 1440;

                                                // Diárias completas
                                                $dias = intdiv($minutos, $minutosPorDiaria);

                                                // Resto de minutos após remover as 24h completas
                                                $resto = $minutos % $minutosPorDiaria;

                                                // Tolerância de 20 minutos → só conta nova diária se passar disso
                                                if ($resto > 20) {
                                                    $dias++;
                                                }

                                                // Garante pelo menos 1 diária
                                                if ($dias <= 0) {
                                                    $dias = 1;
                                                }

                                                // Set quantidade de diárias
                                                $set('quantidade_diarias', $dias);

                                                // Calcula valor total
                                                $valorTotal = $valorDiaria * $dias;

                                                $valorTotal += $valorAcrescimo;
                                                $valorTotal -= $valorDesconto;

                                                // Formata no padrão brasileiro
                                                $valorFormatado = number_format($valorTotal, 2, ',', '.');

                                                // Atribui o valor formatado
                                                $set('valor_total_aluguel', $valorFormatado);

                                                // Unificar os dois repeaters em um só array
                                                $movimentosExistentes = $get('movimentos_existentes') ?? [];
                                                $movimentosNovos      = $get('movimentos_novos') ?? [];

                                                // Mescla preservando índices mas isso não importa para soma
                                                $todosMovimentos = array_merge($movimentosExistentes, $movimentosNovos);

                                                // Recalcula totais de pagamentos
                                                self::atualizarTotaisPagamento($todosMovimentos, $set, $get);
                                            } else {
                                                // Se não tiver datas válidas, zera
                                                $set('quantidade_diarias', null);
                                                $set('valor_total_aluguel', '0,00');
                                            }
                                        })
                                        ->required()
                                        ->maxDate(now()->addMinutes(20)),

                                    Hidden::make('status')
                                        ->default($record->status),
                                ]),

                            Step::make('Pagamentos e Resumo')
                                ->description('Revise os pagamentos e o resumo financeiro')
                                ->schema([
                                    Grid::make()
                                        ->columns(3)
                                        ->components([
                                            Section::make('Resumo Financeiro')
                                                ->columnSpan(1)
                                                ->collapsible(fn($record) => in_array($record->status, ['ativo']))
                                                ->icon('heroicon-o-calculator')
                                                ->description('Valores do aluguel')
                                                ->schema([

                                                    // Quantidade de Diárias (calculado automaticamente)
                                                    TextInput::make('quantidade_diarias')
                                                        ->label('Quantidade de Diárias')
                                                        ->numeric()
                                                        ->disabled()
                                                        ->dehydrated()
                                                        ->suffix('dia(s)')
                                                        ->helperText('Calculado automaticamente pelas datas'),

                                                    // Valor da Diária (editável)
                                                    Money::make('valor_diaria')
                                                        ->label('Valor da Diária')
                                                        ->required()
                                                        ->live()
                                                        ->disabled()
                                                        ->dehydrated()
                                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                                        ->helperText('Valor por dia de aluguel'),

                                                    // Acréscimos
                                                    Money::make('valor_acrescimo_aluguel')
                                                        ->label('(+)Acréscimos')
                                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                                        ->helperText('Taxas, multas, etc.'),

                                                    // Descontos
                                                    Money::make('valor_desconto_aluguel')
                                                        ->label('(-)Descontos')
                                                        ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                                        ->helperText('Promoções, cortesias, etc.'),

                                                    // Valor Total (readonly)
                                                    Money::make('valor_total_aluguel')
                                                        ->label('Valor Total')
                                                        ->required()
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
                                                                ->extraAttributes(['class' => 'text-green-600 font-semibold']),

                                                            // Saldo Restante (calculado)
                                                            Money::make('valor_saldo_aluguel')
                                                                ->label('Saldo Restante')
                                                                ->extraAttributes(['class' => 'text-red-600 font-bold text-lg']),
                                                        ])
                                                        ->columnSpanFull(),
                                                ]),

                                            Section::make('Registrar Pagamentos')
                                                ->columnSpan(2)
                                                ->collapsible(fn($record) => in_array($record->status, ['ativo']))
                                                ->icon('heroicon-o-banknotes')
                                                ->description('Adicione os pagamentos recebidos')
                                                ->headerActions([
                                                    // Você pode adicionar actions aqui se necessário
                                                ])
                                                ->schema([

                                                    Repeater::make('movimentos_existentes')
                                                        ->label('Movimentos de Caixa Registrados')
                                                        ->columns(4)
                                                        ->collapsible()
                                                        ->collapsed()
                                                        ->addable(false)
                                                        ->deletable(false)
                                                        ->reorderable(false)
                                                        ->dehydrated(false) // Não enviar esses dados no submit
                                                        ->itemLabel(fn(array $state): ?string => $state['descricao'] ?? 'Sem descrição')
                                                        ->visible(fn($record) => $record->movimentos()->exists())
                                                        ->schema([
                                                            ToggleButtons::make('metodo_pagamento_id')
                                                                ->disabled()
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
                                                                ->disabled()
                                                                ->label('Bandeira do Cartão')
                                                                ->options(
                                                                    fn() => \App\Models\BandeiraCartaoPagamento::query()
                                                                        ->pluck('bandeira', 'id')
                                                                        ->toArray()
                                                                )
                                                                ->searchable()
                                                                ->visible(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                                ->required(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3]))
                                                                ->columnSpan(4),

                                                            // Número de Autorização (condicional)
                                                            TextInput::make('autorizacao')
                                                                ->disabled()
                                                                ->label('Nº Autorização')
                                                                ->placeholder('000000')
                                                                ->maxLength(20)
                                                                ->visible(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3, 4]))
                                                                ->columnSpan(4),



                                                            // Valor Pago pelo Cliente
                                                            Money::make('valor_pago_movimento')
                                                                ->disabled()
                                                                ->label('Valor Pago')
                                                                ->required()
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
                                                            Money::make('valor_recebido_movimento')
                                                                ->disabled()
                                                                ->label('Valor Recebido')
                                                                ->live(true)
                                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                    $valorRecebido = floatval($state ?? 0);
                                                                    $valorPago = floatval($get('valor_pago_movimento') ?? 0);

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
                                                                ->disabled()
                                                                ->label('Troco')
                                                                ->readOnly()
                                                                ->extraAttributes(['class' => 'text-red-600 font-semibold'])
                                                                ->helperText('Valor que será devolvido ao cliente')
                                                                ->columnSpan(2),


                                                            // Valor Total do Movimento
                                                            Money::make('valor_total_movimento')
                                                                ->disabled()
                                                                ->label('Total')
                                                                ->required()
                                                                ->readOnly()
                                                                ->extraAttributes(['class' => 'font-bold text-lg text-green-600'])
                                                                ->columnSpan(2),
                                                        ]),


                                                    Repeater::make('movimentos_novos')
                                                        ->collapsed()
                                                        ->addActionLabel('Adicionar Pagamento')
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['valor_total_movimento'])
                                                                ? 'Pagamento: R$ ' . number_format((float) $state['valor_total_movimento'], 2, ',', '.')
                                                                : 'Novo Pagamento'
                                                        )
                                                        ->columns(4)
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                            // Recalcular totais quando os movimentos mudarem
                                                            // Unificar os dois repeaters em um só array
                                                            $movimentosExistentes = $get('movimentos_existentes') ?? [];
                                                            $movimentosNovos      = $get('movimentos_novos') ?? [];

                                                            // Mescla preservando índices mas isso não importa para soma
                                                            $todosMovimentos = array_merge($movimentosExistentes, $movimentosNovos);

                                                            // Recalcula totais de pagamentos
                                                            self::atualizarTotaisPagamento($todosMovimentos, $set, $get);
                                                        })
                                                        ->schema([
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
                                                                ->options(
                                                                    fn() => \App\Models\BandeiraCartaoPagamento::query()
                                                                        ->pluck('bandeira', 'id')
                                                                        ->toArray()
                                                                )
                                                                ->searchable()
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
                                                                    $valorPago = self::normalizeMoney($get('valor_pago_movimento') ?? 0);

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
                                                                ->columnSpan(2),

                                                            // Valor Total do Movimento
                                                            Money::make('valor_total_movimento')
                                                                ->label('Total')
                                                                ->required()
                                                                ->readOnly()
                                                                ->extraAttributes(['class' => 'font-bold text-lg text-green-600'])
                                                                ->columnSpan(2),
                                                        ]),
                                                ]),
                                        ]),
                                ]),
                        ]),

                    ])
                    ->modalWidth(function (Aluguel $record): Width {
                        return Width::FourExtraLarge;
                    })->action(function (Aluguel $record, array $data) {
                        DB::beginTransaction();

                        try {
                            // 1️⃣ PROCESSAR NOVOS MOVIMENTOS
                            $totalMovimentos = 0;

                            if (isset($data['movimentos_novos']) && !empty($data['movimentos_novos'])) {
                                foreach ($data['movimentos_novos'] as $movimento) {
                                    // Validar valor do movimento
                                    $valorPago = floatval($movimento['valor_total_movimento'] ?? 0);

                                    if ($valorPago <= 0) {
                                        continue;
                                    }
                                    // Criar o movimento
                                    $novoMovimento = $record->movimentos()->create([
                                        'user_id' => filament()->auth()->id(),
                                        'tipo' => 'entrada',
                                        'metodo_pagamento_id' => $movimento['metodo_pagamento_id'] ?? null,
                                        'cartao_pagamento_id' => $movimento['cartao_pagamento_id'] ?? null,
                                        'autorizacao' => $movimento['autorizacao'] ?? null,
                                        'valor_pago_movimento' => $movimento['valor_pago_movimento'],
                                        'valor_recebido_movimento' => $movimento['valor_recebido_movimento'],
                                        'troco_movimento' => $movimento['troco_movimento'],
                                        'valor_total_movimento' => $movimento['valor_total_movimento'],
                                    ]);

                                    $totalMovimentos++;

                                    Log::info("💰 Movimento criado", [
                                        'movimento_id' => $novoMovimento->id,
                                        'aluguel_id' => $record->id,
                                        'tipo' => 'entrada',
                                        'valor_pago' => $movimento['valor_pago_movimento'],
                                        'valor_total' => $movimento['valor_total_movimento'],
                                        'troco' => $movimento['troco_movimento'],
                                    ]);
                                }
                            }

                            // 2️⃣ RECALCULAR VALORES DO ALUGUEL
                            $valorPagoAnterior = self::normalizeMoney($record->valor_pago_aluguel);
                            $valorPagoAtualizado = self::normalizeMoney($data['valor_pago_aluguel']);

                            // Calcular saldo restante
                            $valorTotal = $data['valor_total_aluguel'] ?? $record->valor_total_aluguel;
                            $saldoAtual = $data['valor_saldo_aluguel'] ?? $record->valor_saldo_aluguel;

                            // 3️⃣ DETERMINAR STATUS
                            $novoStatus = $saldoAtual > 0 ? 'pendente' : 'finalizado';

                            // 4️⃣ ATUALIZAR ALUGUEL
                            $dadosAtualizacao = [
                                'status' => $novoStatus,
                                'data_devolucao_real' => $data['data_devolucao_real'] ?? $record->data_devolucao_real,
                                'quantidade_diarias' => $data['quantidade_diarias'] ?? $record->quantidade_diarias,
                                'valor_diaria' => $data['valor_diaria'] ?? $record->valor_diaria,
                                'valor_acrescimo_aluguel' => $data['valor_acrescimo_aluguel'] ?? $record->valor_acrescimo_aluguel ?? 0,
                                'valor_desconto_aluguel' => $data['valor_desconto_aluguel'] ?? $record->valor_desconto_aluguel ?? 0,
                                'valor_total_aluguel' => $data['valor_total_aluguel'],
                                'valor_pago_aluguel' => $data['valor_pago_aluguel'],
                                'valor_saldo_aluguel' => $data['valor_saldo_aluguel'],
                            ];

                            $record->update($dadosAtualizacao);

                            Log::info("📋 Aluguel atualizado", [
                                'aluguel_id' => $record->id,
                                'status_anterior' => $record->getOriginal('status'),
                                'status_novo' => $novoStatus,
                                'valor_pago_anterior' => $valorPagoAnterior,
                                'valor_pago_novo' => $valorPagoAtualizado,
                                'movimentos_criados' => $totalMovimentos,
                            ]);

                            // 5️⃣ LIBERAR CARRETA (com validação)
                            if (($novoStatus === 'finalizado' || $novoStatus === 'pendente') && $record->carreta) {

                                // Verificar se a carreta está ativa em outro aluguel
                                $aluguelAtivo = Aluguel::where('carreta_id', $record->carreta_id)
                                    ->where('id', '!=', $record->id) // Excluir o aluguel atual
                                    ->whereIn('status', ['ativo']) // Status que indicam uso da carreta
                                    ->first();

                                if (empty($aluguelAtivo)) {
                                    // Não há outro aluguel ativo, pode liberar a carreta
                                    $record->carreta->update(['status' => 'disponivel']);

                                    Log::info("🚛 Carreta liberada", [
                                        'carreta_id' => $record->carreta_id,
                                        'carreta_identificacao' => $record->carreta->identificacao,
                                        'aluguel_id' => $record->id,
                                        'status_aluguel' => $novoStatus,
                                    ]);
                                } else {
                                    // Há outro aluguel ativo, manter carreta como alugada
                                    Log::warning("⚠️ Carreta em uso por outro aluguel", [
                                        'carreta_id' => $record->carreta_id,
                                        'carreta_identificacao' => $record->carreta->identificacao,
                                        'aluguel_atual_id' => $record->id,
                                        'status_aluguel' => $novoStatus,
                                    ]);

                                    Notification::make()
                                        ->warning()
                                        ->title('Carreta não liberada')
                                        ->body("A carreta está sendo utilizada no Aluguel #{$aluguelAtivo->id} ativo.")
                                        ->send();
                                }
                            }

                            DB::commit();

                            // 6️⃣ MENSAGEM DE SUCESSO
                            $mensagemCorpo = [];

                            if ($totalMovimentos > 0) {
                                $mensagemCorpo[] = "{$totalMovimentos} movimento(s) registrado(s).";
                            }

                            if ($saldoAtual > 0) {
                                $mensagemCorpo[] = "Status {$novoStatus}, Carreta {$record->carreta->identificacao} liberada e Saldo restante: R$ " . number_format($saldoAtual, 2, ',', '.');
                            } else {
                                $mensagemCorpo[] = "✅ Aluguel finalizado e carreta liberada!";
                            }

                            Notification::make()
                                ->success()
                                ->title("Aluguel atualizado com sucesso!")
                                ->body(implode(' ', $mensagemCorpo))
                                ->duration(5000)
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Log::error("❌ Erro ao atualizar aluguel", [
                                'aluguel_id' => $record->id,
                                'erro' => $e->getMessage(),
                                'linha' => $e->getLine(),
                                'arquivo' => $e->getFile(),
                                'trace' => $e->getTraceAsString(),
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Erro ao atualizar o aluguel')
                                ->body('Ocorreu um erro: ' . $e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),

                // Action para Cancelar
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'ativo')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Aluguel')
                    ->modalDescription('Tem certeza que deseja cancelar este aluguel?')
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo do Cancelamento')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->cancelar($data['motivo']);

                        // O Observer vai liberar a carreta automaticamente

                        Notification::make()
                            ->success()
                            ->title('Aluguel cancelado')
                            ->body("Carreta {$record->carreta->identificacao} foi liberada.")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                /*BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),*/])
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
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

        // Unificar os dois repeaters em um só array
        $movimentosExistentes = $get('movimentos_existentes') ?? [];
        $movimentosNovos      = $get('movimentos_novos') ?? [];

        // Mescla preservando índices mas isso não importa para soma
        $todosMovimentos = array_merge($movimentosExistentes, $movimentosNovos);

        // Agora envia o array unificado para o totalizador
        self::atualizarTotaisPagamento($todosMovimentos, $set, $get);
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
