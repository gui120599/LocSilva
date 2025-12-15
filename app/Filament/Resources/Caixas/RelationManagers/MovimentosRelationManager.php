<?php

namespace App\Filament\Resources\Caixas\RelationManagers;

use App\Models\MetodoPagamento;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;

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
                            ->default(filament()->auth()->id()),
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

                        // Valor Pago pelo Cliente
                        Money::make('valor_pago_movimento')
                            ->label('Valor Pago')
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled(fn($get) => $get('tipo') === 'saida')
                            ->required()
                            ->live(true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $valorPago = self::normalizeMoney($state ?? 0);
                                $metodoPagamentoId = $get('metodo_pagamento_id');

                                // Buscar o método de pagamento
                                $metodo = MetodoPagamento::find($metodoPagamentoId);

                                // Valores atuais já existentes (normalizados)
                                $valorAcrescimoAtual = self::normalizeMoney($get('valor_acrescimo'));
                                $valorDescontoAtual = self::normalizeMoney($get('valor_desconto'));

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
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled(fn($get) => $get('tipo') === 'saida')
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
                            ->visible(fn($get) => $get('tipo') === 'entrada')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'text-red-600 font-semibold'])
                            ->helperText('Valor que será devolvido ao cliente')
                            ->columnSpan(2),


                        // Valor Total do Movimento
                        Money::make('valor_total_movimento')
                            ->label('Total')
                            ->disabled(fn($get) => $get('tipo') === 'entrada')
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold text-lg text-green-600'])
                            ->columnSpan(3),
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
                    ->recordSelectOptionsQuery(
                        fn(Builder $query) => $query
                            ->where('caixa_id', null)
                    ),
                CreateAction::make()
                    ->label('Novo Movimento'),

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
     * Calcula o total de um movimento específico
     */
    protected static function calcularTotalMovimento(Set $set, Get $get): void
    {
        $valorPago = self::normalizeMoney($get('valor_pago_movimento'));
        $valorAcrescimo = self::normalizeMoney($get('valor_acrescimo_movimento'));
        $valorDesconto = self::normalizeMoney($get('valor_desconto_movimento'));

        $valorTotal = $valorPago + $valorAcrescimo - $valorDesconto;

        $set('valor_total_movimento', number_format($valorTotal, 2, ',', '.'));
    }
}
