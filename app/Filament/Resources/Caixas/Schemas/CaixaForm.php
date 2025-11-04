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
                    ->columnSpan(1)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        DateTimePicker::make('data_abertura')
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
                    ->icon('heroicon-o-banknotes')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('movimentos')
                            ->columns(4)
                            ->label('Pagamentos')
                            ->relationship()
                            ->schema([
                                TextInput::make('descricao')
                                    ->default('Saldo Inicial')
                                    ->readonly()
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->default(auth()->user()->id),
                                Select::make('tipo')
                                    ->hidden()
                                    ->options(['entrada' => 'Entrada', 'saida' => 'Saida'])
                                    ->default('entrada')
                                    ->required(),
                                ToggleButtons::make('metodo_pagamento_id')
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
                                    ->relationship('bandeiraCartao', 'bandeira')
                                    // Tornar este campo visível APENAS se o método de pagamento for 'Cartão' (opcional)
                                    ->visible(function ($get) {
                                        // Pega o NOME do método de pagamento
                                        $metodoNome = $get('metodoPagamento.nome');

                                        // Se a relação não carregar o nome, use o ID e procure manualmente
                                        // Se a relação estiver bem definida, o código acima funciona
                                        return $metodoNome === 'Cartão';
                                    }),

                                TextInput::make('autorizacao')
                                    ->label('Nº de Autorização da Transação')
                                    // Define a visibilidade do campo 'autorizacao'
                                    ->visible(function ($get) {
                                        // Captura o nome do método de pagamento selecionado.
                                        // O '.nome' funciona porque o Select acima usa 'metodoPagamento.nome' na relação.
                                        $metodoNome = $get('metodoPagamento.nome');

                                        // Verifica se o nome está na lista de métodos que exigem autorização.
                                        return in_array($metodoNome, ['Cartão', 'Pix']);

                                        // Caso você precise usar o ID (mais seguro), use o ID:
                                        // $metodoId = $get('metodo_pagamento_id'); 
                                        // return in_array($metodoId, [1, 2]); // Ex: 1 para Cartão, 2 para Pix
                                    }),

                                TextInput::make('valor_recebido')
                                    ->live()
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
                                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                        // Atualiza o valor_total quando valor_recebido muda
                                        $set('valor_total', $state);
                                    }),
                                TextInput::make('valor_total')
                                    ->live()
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
                            ])

                    ])

            ]);
    }
}
