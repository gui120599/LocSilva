<?php

namespace App\Filament\Resources\Caixas\RelationManagers;

use App\Models\MetodoPagamento;
use App\Models\User;
use Filament\Actions\AssociateAction;
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
                Select::make('aluguel_id')
                    ->relationship('aluguel', 'id'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('descricao'),
                Select::make('tipo')
                    ->options(['entrada' => 'Entrada', 'saida' => 'Saida'])
                    ->default('entrada')
                    ->required(),
                Select::make('metodo_pagamento_id')
                    ->relationship('metodoPagamento', 'id')
                    ->required(),
                TextInput::make('cartao_pagamento_id')
                    ->numeric(),
                TextInput::make('autorizacao'),
                TextInput::make('valor_pago')
                    ->required()
                    ->numeric(),
                TextInput::make('valor_recebido')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_acrescimo')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_desconto')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('troco')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
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
                    ->searchable(),
                TextColumn::make('descricao')
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
                    }),
                TextColumn::make('metodoPagamento.nome')
                    ->icon(fn(string $state) => match ($state) {
                        'Dinheiro' => Heroicon::Banknotes,
                        'Cartão de Crédito' => Heroicon::CreditCard,
                        'Cartão de Débito' => Heroicon::CreditCard,
                        'Pix' => Heroicon::QrCode
                    })
                    ->searchable(),
                TextColumn::make('cartao_pagamento_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('autorizacao')
                    ->searchable(),
                TextColumn::make('valor_pago')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_recebido')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_acrescimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_desconto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('troco')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_total')
                    ->numeric()
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
                                TextInput::make('valor_pago')
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

                                TextInput::make('valor_recebido')
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

                                TextInput::make('troco')
                                    ->visible(fn($get) => $get('tipo') === 'entrada')
                                    ->disabled(fn($get) => $get('tipo') === 'saida')
                                    ->readOnly()
                                    ->columnSpan(1)
                                    ->live()
                                    ->required()
                                    ->prefix('R$')
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->placeholder('0,00'),

                                TextInput::make('valor_total')
                                    ->readOnly()
                                    ->columnSpanFull()
                                    ->prefix('R$')
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.'))
                                    ->placeholder('0,00'),

                            ]),
                    ]),
                AssociateAction::make()
                    ->label('Associar Movimento')
                    ->recordSelectSearchColumns(['descricao', 'id'])
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
