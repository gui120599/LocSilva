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
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;

class CaixaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Dados do Caixa')
                    ->columnSpan(fn (string $context) => $context === 'edit' ? 'full' : 1)
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

                                TextInput::make('valor_recebido')
                                    ->columnSpan(3)
                                    ->live(true)
                                    ->required()
                                    ->prefix('R$')
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
                                    ->afterStateUpdated(function (callable $set, $state, callable $get, TextInput $component) {

                                        $set('valor_total', $state);
                                        $set('saldo_inicial', $state);
                                    }),
                                TextInput::make('valor_total')
                                    ->readOnly()
                                    ->columnSpan(1)
                                    ->prefix('R$')
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
                            ])

                    ])

            ]);
    }
}
