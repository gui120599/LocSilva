<?php

namespace App\Filament\Resources\Carretas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CarretaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('identificacao')
                    ->required(),
                Select::make('tipo')
                    ->options(['carreta' => 'Carreta', 'reboque' => 'Reboque'])
                    ->required(),
                TextInput::make('marca'),
                TextInput::make('modelo'),
                TextInput::make('ano')
                    ->numeric(),
                TextInput::make('placa'),
                TextInput::make('capacidade_carga')
                    ->numeric(),
                TextInput::make('valor_diaria')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['disponivel' => 'Disponivel', 'alugada' => 'Alugada', 'manutencao' => 'Manutencao'])
                    ->default('disponivel')
                    ->required(),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
