<?php

namespace App\Filament\Resources\Caixas\RelationManagers;

use App\Models\MetodoPagamento;
use Filament\Actions\AssociateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimentosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimentos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        ToggleButtons::make('tipo')
                            ->live()
                            ->columnSpanFull()
                            ->grouped()
                            ->label('Tipo de Movimentação')
                            ->options([
                                'entrada' => 'Entrada',
                                'saida' => 'Saida',
                            ])
                            ->icons([
                                'entrada' => Heroicon::PlusCircle,
                                'saida' => Heroicon::MinusCircle,
                            ])
                            ->colors([
                                'entrada' => 'success',
                                'saida' => 'danger',
                            ])
                            ->default('entrada')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('descricao')
                            ->placeholder('Descreva a movimentação do caixa')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->dehydrated()
                            ->default(auth()->user()->id),
                        ToggleButtons::make('metodo_pagamento_id')
                            ->live()
                            ->label('Método do Pagamento')
                            ->columnSpanFull()
                            ->options(
                                function () {
                                    return MetodoPagamento::pluck('nome', 'id')->toArray();
                                }
                            )
                            ->icons([
                                1 => Heroicon::Banknotes,
                                2 => Heroicon::CreditCard,
                                3 => Heroicon::CreditCard,
                                4 => Heroicon::QrCode
                            ])
                            ->default(1)
                            ->grouped(),

                        Select::make('cartao_pagamento_id')
                            ->columnSpan(1)
                            ->relationship('bandeiraCartao', 'bandeira')
                            ->visible(function ($get) {
                                $metodoId = $get('metodo_pagamento_id');
                                return in_array($metodoId, [2, 3]);
                            }),

                        TextInput::make('autorizacao')
                            ->columnSpan(4)
                            ->label('Nº de Autorização da Transação')
                            ->visible(function ($get) {
                                $metodoId = $get('metodo_pagamento_id');
                                return in_array($metodoId, [2, 3, 4]);
                            }),
                        TextInput::make('valor_pago_movimento')
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled(fn($get) => $get('tipo') === 'saida')
                            ->columnSpan(2)
                            ->live()
                            ->required()
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                        $money($input, ',', '.', 2)
                                    JS))
                            ->dehydrateStateUsing(function ($state) {
                                if (!$state)
                                    return 0;
                                $value = preg_replace('/[^\d,]/', '', $state); // mantém só números e vírgula
                                return (float) str_replace(',', '.', $value);  // troca vírgula por ponto
                            })
                            ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                            ->placeholder('0,00')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $valorPago = str_replace(['R$', '.', ' '], '', $get('valor_pago') ?? '0');
                                $valorPago = (float) str_replace(',', '.', $valorPago);

                                $valorRecebido = str_replace(['R$', '.', ' '], '', $get('valor_recebido') ?? '0');
                                $valorRecebido = (float) str_replace(',', '.', $valorRecebido);

                                $troco = max($valorRecebido - $valorPago, 0);

                                $set('troco', $troco);
                                $set('valor_total', $valorPago);
                            }),

                        TextInput::make('valor_recebido_movimento')
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled(fn($get) => $get('tipo') === 'saida')
                            ->columnSpan(2)
                            ->live()
                            ->required()
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->dehydrateStateUsing(function ($state) {
                                if (!$state)
                                    return 0;
                                $value = preg_replace('/[^\d,]/', '', $state);
                                return (float) str_replace(',', '.', $value);
                            })
                            ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                            ->placeholder('0,00')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $valorPago = str_replace(['R$', '.', ' '], '', $get('valor_pago') ?? '0');
                                $valorPago = (float) str_replace(',', '.', $valorPago);

                                $valorRecebido = str_replace(['R$', '.', ' '], '', $get('valor_recebido') ?? '0');
                                $valorRecebido = (float) str_replace(',', '.', $valorRecebido);

                                $troco = max($valorRecebido - $valorPago, 0);

                                $set('troco', $troco);
                                $set('valor_total', $valorPago);
                            }),

                        TextInput::make('troco_movimento')
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled(fn($get) => $get('tipo') === 'saida')
                            ->readOnly()
                            ->columnSpan(1)
                            ->live()
                            ->required()
                            ->prefix('R$')
                            ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                            ->placeholder('0,00'),

                        TextInput::make('valor_total_movimento')
                            ->readOnly(fn($get) => $get('tipo') === 'entrada')
                            ->columnSpanFull()
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                        $money($input, ',', '.', 2)
                                    JS))
                            ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                            ->dehydrateStateUsing(function ($state) {
                                if (!$state)
                                    return 0;
                                $value = preg_replace('/[^\d,]/', '', $state);
                                return (float) str_replace(',', '.', $value);
                            })
                            ->placeholder('0,00'),

                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->columns([
                TextColumn::make('aluguel.id')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('descricao')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->icon(fn(string $state) => match ($state) {
                        'entrada' => Heroicon::PlusCircle,
                        'saida' => Heroicon::MinusCircle,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'entrada' => 'success',
                        'saida' => 'danger',
                        default => 'secondary',
                    })
                    ->toggleable(),
                TextColumn::make('metodoPagamento.nome')
                    ->icon(fn(string $state) => match ($state) {
                        'Dinheiro' => Heroicon::Banknotes,
                        'Cartão de Crédito' => Heroicon::CreditCard,
                        'Cartão de Débito' => Heroicon::CreditCard,
                        'Pix' => Heroicon::QrCode
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cartao_pagamento_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('autorizacao')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valor_pago_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valor_recebido_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valor_acrescimo_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valor_desconto_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('troco_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valor_total_movimento')
                    ->money('BRL', decimalPlaces: 2)
                    ->sortable(),
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
                TrashedFilter::make(),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Associar Movimento')
                    ->recordSelectSearchColumns(['descricao', 'id', 'valor_total_movimento'])
                    ->multiple()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn(Builder $query) => $query
                        ->where('caixa_id', null)
                    ),
                CreateAction::make()
                    ->schema([
                        Section::make()
                            ->columns(5)
                            ->schema([
                                ToggleButtons::make('tipo')
                                    ->live()
                                    ->columnSpanFull()
                                    ->grouped()
                                    ->label('Tipo de Movimentação')
                                    ->options([
                                        'entrada' => 'Entrada',
                                        'saida' => 'Saida',
                                    ])
                                    ->icons([
                                        'entrada' => Heroicon::PlusCircle,
                                        'saida' => Heroicon::MinusCircle,
                                    ])
                                    ->colors([
                                        'entrada' => 'success',
                                        'saida' => 'danger',
                                    ])
                                    ->default('entrada')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('descricao')
                                    ->placeholder('Descreva a movimentação do caixa')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('user_id')
                                    ->label('Responsável')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(auth()->user()->id),
                                ToggleButtons::make('metodo_pagamento_id')
                                    ->live()
                                    ->label('Método do Pagamento')
                                    ->columnSpanFull()
                                    ->options(
                                        function () {
                                            return MetodoPagamento::pluck('nome', 'id')->toArray();
                                        }
                                    )
                                    ->icons([
                                        1 => Heroicon::Banknotes,
                                        2 => Heroicon::CreditCard,
                                        3 => Heroicon::CreditCard,
                                        4 => Heroicon::QrCode
                                    ])
                                    ->default(1)
                                    ->grouped(),

                                Select::make('cartao_pagamento_id')
                                    ->columnSpan(1)
                                    ->relationship('bandeiraCartao', 'bandeira')
                                    ->visible(function ($get) {
                                        $metodoId = $get('metodo_pagamento_id');
                                        return in_array($metodoId, [2, 3]);
                                    }),

                                TextInput::make('autorizacao')
                                    ->columnSpan(4)
                                    ->label('Nº de Autorização da Transação')
                                    ->visible(function ($get) {
                                        $metodoId = $get('metodo_pagamento_id');
                                        return in_array($metodoId, [2, 3, 4]);
                                    }),
                                TextInput::make('valor_pago_movimento')
                                    ->visible(fn($get) => $get('tipo') === 'entrada')
                                    ->disabled(fn($get) => $get('tipo') === 'saida')
                                    ->columnSpan(2)
                                    ->live()
                                    ->required()
                                    ->prefix('R$')
                                    ->mask(RawJs::make(<<<'JS'
        $money($input, ',', '.', 2)
    JS))
                                    ->dehydrateStateUsing(function ($state) {
                                        if (!$state)
                                            return 0;
                                        $value = preg_replace('/[^\d,]/', '', $state); // mantém só números e vírgula
                                        return (float) str_replace(',', '.', $value);  // troca vírgula por ponto
                                    })
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->placeholder('0,00')
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $valorPago = str_replace(['R$', '.', ' '], '', $get('valor_pago') ?? '0');
                                        $valorPago = (float) str_replace(',', '.', $valorPago);

                                        $valorRecebido = str_replace(['R$', '.', ' '], '', $get('valor_recebido') ?? '0');
                                        $valorRecebido = (float) str_replace(',', '.', $valorRecebido);

                                        $troco = max($valorRecebido - $valorPago, 0);

                                        $set('troco', $troco);
                                        $set('valor_total', $valorPago);
                                    }),

                                TextInput::make('valor_recebido_movimento')
                                    ->visible(fn($get) => $get('tipo') === 'entrada')
                                    ->disabled(fn($get) => $get('tipo') === 'saida')
                                    ->columnSpan(2)
                                    ->live()
                                    ->required()
                                    ->prefix('R$')
                                    ->mask(RawJs::make(<<<'JS'
        $money($input, ',', '.', 2)
    JS))
                                    ->dehydrateStateUsing(function ($state) {
                                        if (!$state)
                                            return 0;
                                        $value = preg_replace('/[^\d,]/', '', $state);
                                        return (float) str_replace(',', '.', $value);
                                    })
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->placeholder('0,00')
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $valorPago = str_replace(['R$', '.', ' '], '', $get('valor_pago') ?? '0');
                                        $valorPago = (float) str_replace(',', '.', $valorPago);

                                        $valorRecebido = str_replace(['R$', '.', ' '], '', $get('valor_recebido') ?? '0');
                                        $valorRecebido = (float) str_replace(',', '.', $valorRecebido);

                                        $troco = max($valorRecebido - $valorPago, 0);

                                        $set('troco', $troco);
                                        $set('valor_total', $valorPago);
                                    }),

                                TextInput::make('troco_movimento')
                                    ->visible(fn($get) => $get('tipo') === 'entrada')
                                    ->disabled(fn($get) => $get('tipo') === 'saida')
                                    ->readOnly()
                                    ->columnSpan(1)
                                    ->live()
                                    ->required()
                                    ->prefix('R$')
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->placeholder('0,00'),

                                TextInput::make('valor_total_movimento')
                                    ->readOnly(fn($get) => $get('tipo') === 'entrada')
                                    ->columnSpanFull()
                                    ->prefix('R$')
                                    ->mask(RawJs::make(<<<'JS'
                                        $money($input, ',', '.', 2)
                                    JS))
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->dehydrateStateUsing(function ($state) {
                                        if (!$state)
                                            return 0;
                                        $value = preg_replace('/[^\d,]/', '', $state);
                                        return (float) str_replace(',', '.', $value);
                                    })
                                    ->placeholder('0,00'),

                            ]),
                    ]),

            ])
            ->recordActions([
                EditAction::make()
                ->visible(fn($record) => $record->descricao !== 'Saldo Inicial'),
                DissociateAction::make()
                ->visible(fn($record) => $record->descricao !== 'Saldo Inicial'),
                DeleteAction::make()
                ->visible(fn($record) => 
                    $record->aluguel_id === null 
                    && $record->descricao !== 'Saldo Inicial'),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    //DeleteBulkAction::make(),
                    //ForceDeleteBulkAction::make(),
                    //RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
