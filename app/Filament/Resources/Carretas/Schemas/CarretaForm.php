<?php

namespace App\Filament\Resources\Carretas\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class CarretaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->description('Dados da Carreta/Reboque')
                    ->icon('heroicon-s-truck')
                    ->columns(6)
                    ->schema([
                        Section::make()
                            ->columnSpan(4)
                            ->columns(2)
                            ->schema([
                                TextInput::make('identificacao')
                                    ->unique()
                                    ->validationMessages([
                                        'unique' => 'O nº de identificação já existe!'
                                    ])
                                    ->required(),
                                TextInput::make('placa'),
                                Select::make('tipo')
                                    ->columnSpan(3)
                                    ->options(['carreta' => 'Carreta', 'reboque' => 'Reboque'])
                                    ->required(),

                                TextInput::make('valor_diaria')
                                    ->label('Valor da Diária')
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
                                    ->placeholder('0,00'),
                                ToggleButtons::make('status')
                                    ->options(['disponivel' => 'Disponivel', 'alugada' => 'Alugada', 'manutencao' => 'Manutencao'])
                                    ->icons(['disponivel' => 'heroicon-o-check-circle', 'alugada' => 'heroicon-o-truck', 'manutencao' => 'heroicon-o-wrench-screwdriver'])
                                    ->colors(['disponivel' => 'success', 'alugada' => 'info', 'manutencao' => 'warning'])
                                    ->default('disponivel')
                                    ->grouped()
                                    ->required(),

                            ]),
                        Section::make()
                            ->columnSpan(2)
                            ->schema([
                                FileUpload::make('foto')
                                    ->disk('public')
                                    ->directory('fotos_carretas')
                                    ->image()
                                    ->maxSize(2048)
                                    ->hint('Tamanho máximo: 2MB'),
                            ]),
                    ]),
                Section::make()
                    ->description('Arquivos')
                    ->icon('heroicon-s-document')
                    ->schema([
                        FileUpload::make('documento')
                            ->hintActions([
                                Action::make('print')
                                    ->icon('heroicon-o-printer')
                                    ->color('primary')
                                    ->url(fn($record) => route('documento.print', $record->id), )
                                    ->visible(fn($record) => $record && $record->documento)
                                    ->openUrlInNewTab(),
                            ])
                            ->disk('public')
                            ->directory('documentos_carretas')
                            ->downloadable(),
                    ]),
                Section::make()
                    ->description('Caracteristicas')
                    ->icon('heroicon-s-swatch')
                    ->columns(5)
                    ->schema([
                        TextInput::make('marca')
                            ->columnSpan(2),
                        TextInput::make('modelo')
                            ->columnSpan(2),
                        TextInput::make('ano')
                            ->columnSpan(1),
                        TextInput::make('capacidade_carga')
                            ->label('Capacidade de Carga (kg)')
                            ->columnSpan(5)
                            ->prefix('KG'),
                    ]),
                Section::make()
                    ->description('Observações')
                    ->icon('heroicon-s-chat-bubble-bottom-center-text')
                    ->columns(5)
                    ->schema([
                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
