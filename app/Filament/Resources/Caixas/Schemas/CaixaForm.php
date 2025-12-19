<?php

namespace App\Filament\Resources\Caixas\Schemas;

use App\Models\MetodoPagamento;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Leandrocfe\FilamentPtbrFormFields\Money;

class CaixaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Dados do Caixa')
                    ->columnSpan(fn(string $context) => $context === 'edit' ? 'full' : 1)
                    ->schema([
                        Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('user', 'name')
                            ->default(auth()->user()->id)
                            ->searchable()
                            ->preload()
                            ->required(),
                        DateTimePicker::make('data_abertura')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('data_fechamento'),
                        Select::make('status')
                            ->options(['aberto' => 'Aberto', 'fechado' => 'Fechado'])
                            ->default('aberto')
                            ->required(),
                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Saldo Inicial')
                    ->visible(fn(string $context) => $context === 'create')
                    ->icon('heroicon-o-banknotes')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('movimentos')
                            ->deletable(false)
                            ->columns(4)
                            ->label('Recebimentos do Saldo Inicial')
                            ->relationship()
                            ->maxItems(1)
                            ->schema([
                                TextInput::make('descricao')
                                    ->default('Saldo Inicial')
                                    ->readonly()
                                    ->required()
                                    ->columnSpanFull(),

                                Select::make('user_id')
                                    ->label('Responsável')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(auth()->user()->id),

                                Select::make('tipo')
                                    ->hidden()
                                    ->options(['entrada' => 'Entrada', 'saida' => 'Saida'])
                                    ->default('entrada')
                                    ->required(),

                                ToggleButtons::make('metodo_pagamento_id')
                                    ->columnSpanFull()
                                    ->inline(fn() => true)
                                    ->live()
                                    ->label('Método do Pagamento')
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
                                    ->default(1),

                                Select::make('cartao_pagamento_id')
                                    ->columnSpan(2)
                                    ->relationship('bandeiraCartao', 'bandeira')
                                    ->visible(function ($get) {
                                        $metodoId = $get('metodo_pagamento_id');
                                        return in_array($metodoId, [2, 3]);
                                    }),

                                TextInput::make('autorizacao')
                                    ->columnSpan(2)
                                    ->label('Nº de Autorização da Transação')
                                    ->visible(function ($get) {
                                        $metodoId = $get('metodo_pagamento_id');
                                        return in_array($metodoId, [2, 3, 4]);
                                    }),


                                Money::make('valor_recebido_movimento')
                                    ->label('Valor Recebido')
                                    ->columnSpan(2)
                                    ->live(true)
                                    ->required()
                                    ->afterStateUpdated(function (callable $set, $state, callable $get, TextInput $component) {
                                        $set('valor_total_movimento', $state);
                                        $set('saldo_inicial', $state);
                                    }),

                                Money::make('valor_total_movimento')
                                    ->label('Valor Total')
                                    ->readOnly()
                                    ->columnSpan(2),
                            ])

                    ])

            ]);
    }
}
