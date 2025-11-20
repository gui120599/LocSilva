<?php

namespace App\Filament\Resources\Aluguels\Tables;

use App\Models\Aluguel;
use App\Models\Carreta;
use App\Models\MetodoPagamento;
use Carbon\Carbon;
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

class AluguelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                TextColumn::make('carreta.identificacao')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('data_retirada')
                    ->label('Retirada')
                    ->color('success')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('data_devolucao_prevista')
                    ->label('Devolução Prevista')
                    ->color('warning')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('data_devolucao_real')
                    ->date('d/m/Y')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->label('Status')
                    //->default(['ativo', 'pendente'])
                    ->options([
                        'ativo' => 'Ativo',
                        'finalizado' => 'Finalizado',
                        'pendente' => 'Pendente',
                        'cancelado' => 'Cancelado',
                    ]),
                Filter::make('data_retirada')
                    ->schema([
                        DatePicker::make('data_retirada_de')
                            ->label('Data de Retirada De')
                            ->placeholder('Data de Retirada De'),
                        DatePicker::make('data_retirada_ate')
                            ->label('Data de Retirada Até')
                            ->placeholder('Data de Retirada Até'),
                    ]),
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome')
                    ->multiple()
                    ->searchable()->preload(10)
            ])
            ->recordActions([

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
                    ->form(fn(Aluguel $record): array => [
                        DateTimePicker::make('data_devolucao_real')
                            ->disabled(fn($record) => in_array($record->status, ['pendente']))
                            ->default($record->data_devolucao_real)
                            ->dehydrated()
                            ->label('Data de Devolução')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) use ($record) {

                                $dataRetirada = $record->data_retirada;
                                $valorDiaria = floatval($record->valor_diaria ?? 0);

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

                                    // Formata no padrão brasileiro
                                    $valorFormatado = number_format($valorTotal, 2, ',', '.');

                                    // Atribui o valor formatado
                                    $set('valor_total_aluguel', $valorFormatado);

                                    // Recalcula totais de pagamentos
                                    self::atualizarTotaisPagamento_2($set, $get);
                                }
                                else {
                                    // Se não tiver datas válidas, zera
                                    $set('quantidade_diarias', null);
                                    $set('valor_total_aluguel', '0,00');
                                }
                            })
                            ->required()
                            ->maxDate(now()),


                        Hidden::make('status')
                            ->default($record->status),

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
                                            ->default(function () use ($record): string {
                                                return $record->quantidade_diarias;
                                            })
                                            ->helperText('Calculado automaticamente pelas datas'),

                                        // Valor da Diária (editável)
                                        TextInput::make('valor_diaria')
                                            ->label('Valor da Diária')
                                            ->required()
                                            ->live()
                                            ->prefix('R$')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(function () use ($record): string {
                                                return number_format($record->valor_diaria ?? 0, 2, ",", ".");
                                            })
                                            ->dehydrateStateUsing(function ($state) {
                                                // Remove formatação antes de salvar
                                                if (!$state)
                                                    return 0;

                                                // Remove R$, pontos e converte vírgula em ponto
                                                $value = str_replace(['R$', '.', ' '], '', $state);
                                                $value = str_replace(',', '.', $value);

                                                return (float) $value;
                                            })
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Valor por dia de aluguel'),

                                        // Acréscimos
                                        TextInput::make('valor_acrescimo_aluguel')
                                            ->label('(+)Acréscimos')
                                            ->default(function () use ($record): string {
                                                return number_format($record->valor_acrescimo_aluguel ?? 0, 2, ",", ".");
                                            })
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
                                            ->prefix('R$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->live()
                                            ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Taxas, multas, etc.'),

                                        // Descontos
                                        TextInput::make('valor_desconto_aluguel')
                                            ->label('(-)Descontos')
                                            ->default(function () use ($record): string {
                                                return number_format($record->valor_desconto_aluguel ?? 0, 2, ",", ".");
                                            })
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
                                            ->prefix('R$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->live()
                                            ->afterStateUpdated(fn($set, $get) => self::calcularValores($set, $get))
                                            ->helperText('Promoções, cortesias, etc.'),

                                        // Valor Total (readonly)
                                        TextInput::make('valor_total_aluguel')
                                            ->label('Valor Total')
                                            ->default(function () use ($record): string {
                                                return number_format($record->valor_total_aluguel ?? 0, 2, ",", ".");
                                            })
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
                                                    ->default(function () use ($record): string {
                                                        return number_format($record->valor_pago_aluguel ?? 0, 2, ",", ".");
                                                    })
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
                                                    ->extraAttributes(['class' => 'text-green-600 font-semibold']),

                                                // Saldo Restante (calculado)
                                                TextInput::make('valor_saldo_aluguel')
                                                    ->label('Saldo Restante')
                                                    ->default(function () use ($record): string {
                                                        return number_format($record->valor_saldo_aluguel ?? 0, 2, ",", ".");
                                                    })
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
                                                    ->extraAttributes(['class' => 'text-red-600 font-bold text-lg']),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Registrar Pagamentos')
                                    ->columnSpan(2)
                                     ->visible(function () use ($record): bool {
                                        // Calcula o saldo restante (Total - Pago)
                                        $saldoRestante = $record->valor_total_aluguel - $record->movimentos->sum('valor_total_movimento');
                                        // Retorna true (visível) se o saldo for maior que zero (usando 0.01 para segurança de floats)
                                        return $saldoRestante > 0.01;
                                    })
                                    ->collapsible(fn($record) => in_array($record->status, ['ativo']))
                                    ->icon('heroicon-o-banknotes')
                                    ->description('Adicione os pagamentos recebidos')
                                    ->headerActions([
                                        // Você pode adicionar actions aqui se necessário
                                    ])
                                    ->schema([
                                        
                                        Repeater::make('movimentos')
                                            ->relationship()
                                            ->default($record->movimentos->toArray())
                                            ->collapsed()
                                            ->addActionLabel('Adicionar Pagamento')
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                isset($state['valor_total_movimento'])
                                                ? 'Pagamento: R$ ' . number_format((float) $state['valor_total_movimento'], 2, ',', '.')
                                                : 'Novo Pagamento'
                                            )
                                            ->columns(4)
                                            ->schema([

                                                // Descrição
                                                TextInput::make('descricao')
                                                    ->label('Descrição')
                                                    ->placeholder('Ex: Pagamento inicial, Parcela 1/3, etc.')
                                                    ->default(function () use ($record): string {
                                                        return "Pagamento restante Aluguel ID - {$record->id}";
                                                    })
                                                    ->columnSpan(4),

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
                                                    ->columnSpan(2),

                                                // Número de Autorização (condicional)
                                                TextInput::make('autorizacao')
                                                    ->label('Nº Autorização')
                                                    ->placeholder('000000')
                                                    ->maxLength(20)
                                                    ->visible(fn(Get $get) => in_array($get('metodo_pagamento_id'), [2, 3, 4]))
                                                    ->columnSpan(2),

                                                // Valor Pago pelo Cliente
                                                TextInput::make('valor_pago_movimento')
                                                    ->label('Valor Pago')
                                                    ->required()
                                                    ->prefix('R$')
                                                    ->minValue(0)
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
                                                    ->columnSpan(2),

                                                /*// Acréscimo (taxa)
                                                TextInput::make('valor_acrescimo_movimento')
                                                    ->label('Acréscimo')
                                                    ->numeric()
                                                    ->prefix('R$')
                                                    ->readOnly()
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => floatval($get('valor_acrescimo') ?? 0) > 0)
                                                    ->helperText('Taxa do método de pagamento')
                                                    ->columnSpan(1),

                                                // Desconto (taxa)
                                                TextInput::make('valor_desconto_movimento')
                                                    ->label('Desconto')
                                                    ->numeric()
                                                    ->prefix('R$')
                                                    ->readOnly()
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => floatval($get('valor_desconto') ?? 0) > 0)
                                                    ->helperText('Taxa do método de pagamento')
                                                    ->columnSpan(1),*/

                                                // Valor Total do Movimento
                                                TextInput::make('valor_total_movimento')
                                                    ->label('Total')
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
                        
                    ])
                    ->modalWidth(function (Aluguel $record): Width {

                        
                            return Width::FourExtraLarge;
                        

                    })
                    ->action(function (Aluguel $record, array $data) {


                        DB::beginTransaction();

                        try {

                            // 3️⃣ RE-CALCULAR os valores após o movimento
                            $valorPagoAtualizado = $record->movimentos->sum('valor_total_movimento');
                            $saldoAtual = max(0, $record->valor_total_aluguel - $valorPagoAtualizado);


                            // 4️⃣ Determinar STATUS conforme saldo ou pagamento
                            if ($saldoAtual > 0) {
                                // Tem saldo pendente → pendente
                                $novoStatus = 'pendente';
                            } else {
                                // Saldo zerado → finalizado
                                $novoStatus = 'finalizado';
                            }

                            // 2️⃣ Atualizar a data de devolução
                            $record->update([
                                'status' => $novoStatus,
                                'data_devolucao_real' => $data['data_devolucao_real'],
                                'quantidade_diarias' => $data['quantidade_diarias'],
                                'valor_diaria' => $data['valor_diaria'],
                                'valor_acrescimo_aluguel' => $data['valor_acrescimo_aluguel'],
                                'valor_desconto_aluguel' => $data['valor_desconto_aluguel'],
                                'valor_total_aluguel' => $data['valor_total_aluguel'],
                                'valor_pago_aluguel' => $data['valor_pago_aluguel'],
                                'valor_saldo_aluguel' => $data['valor_saldo_aluguel'],
                            ]);

                            // Liberar a carreta
                            $carreta = Carreta::find($record->carreta_id);

                            $carreta->update([
                                'status' =>'disponivel'
                            ]);

                            DB::commit();


                            // ✅ Mensagem de feedback
                            Notification::make()
                                ->success()
                                ->title("Aluguel atualizado com sucesso!")
                                ->body(
                                    $data['valor_pago_aluguel'] > 0
                                    ? "Recebido R$ " . number_format($data['valor_pago_aluguel'], 2, ',', '.') . ". Status agora: {$novoStatus}."
                                    : "Nenhum pagamento recebido. Status agora: {$novoStatus}."
                                )
                                ->send();

                        } catch (\Exception $e) {

                            DB::rollBack();
                            \Log::error('Erro ao finalizar aluguel: ' . $e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Erro ao atualizar o aluguel')
                                ->body('Ocorreu um erro. Os dados foram revertidos.' . $e->getMessage())
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
                ]),*/
            ])
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    /**
     * Calcula os valores totais do aluguel
     */
    protected static function calcularValores(Set $set, Get $get): void
    {
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
        $totalPago = floatval($get('valor_total_movimento_antigo')) ?? 0;

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

    /**
     * Atualiza os totais de pagamento no resumo
     */
    protected static function atualizarTotaisPagamento_2(Set $set, Get $get): void
    {
        $totalPago = floatval($get('valor_total_movimento_antigo')) ?? 0;

        $valorTotal = floatval($get('valor_total_aluguel') ?? 0);
        $saldo = max(0, $valorTotal - $totalPago);

        $set('valor_pago_aluguel', number_format($totalPago, 2, ',', '.'));
        $set('valor_saldo_aluguel', number_format($saldo, 2, ',', '.'));
    }
}