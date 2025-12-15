<?php

namespace App\Filament\Resources\Adicionals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money;

class AdicionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->icon('heroicon-o-squares-plus')
                    ->description('Dados do Adicional')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        FileUpload::make('foto')
                            ->disk('public')
                            ->directory('fotos_adicionais')
                            ->image()
                            ->maxSize(2048)
                            ->hint('Tamanho máximo: 2MB')
                            ->columnSpanFull(),
                        TextInput::make('descricao')
                            ->columnSpan(3)
                            ->required(),
                        Money::make('valor')
                            ->columnSpan(1)
                            ->required(),
                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
