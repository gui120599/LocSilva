<?php

namespace App\Filament\Resources\Carretas\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->description('Arquivos')
                    ->icon('heroicon-s-document')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('foto')
                        ->columnSpan(1)
                            ->disk('public')
                            ->directory('fotos_carretas'),
                        FileUpload::make('documento')
                        ->columnSpan(1)
                            ->hintActions([
                                Action::make('print')
                                    ->icon('heroicon-o-printer')
                                    ->color('primary')
                                    ->url(fn($record) => route('documento.print', $record->id), )
                                    ->openUrlInNewTab()
                                    ->hidden('create'),
                            ])
                            ->disk('public')
                            ->directory('documentos_carretas')
                            ->downloadable(),
                    ]),
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
                                Select::make('status')
                                    ->options(['disponivel' => 'Disponivel', 'alugada' => 'Alugada', 'manutencao' => 'Manutencao'])
                                    ->default('disponivel')
                                    ->required(),
                                Textarea::make('observacoes')
                                    ->columnSpanFull(),
                            ]),

                    ]),
                Section::make()
                    ->description('Caracteristicas')
                    ->icon('heroicon-s-swacht')
                    ->schema([
                        TextInput::make('marca')
                            ->columnSpan(2),
                        TextInput::make('modelo')
                            ->columnSpan(2),
                        TextInput::make('ano')
                            ->numeric(),
                        TextInput::make('capacidade_carga')
                            ->label('Capacidade de Carga (kg)')
                            ->columnSpan(2)
                            ->prefix('KG')
                            ->numeric(),
                    ])
            ]);
    }
}
