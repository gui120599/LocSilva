<?php

namespace App\Filament\Resources\Caixas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CaixaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('data_abertura')
                    ->required(),
                DateTimePicker::make('data_fechamento'),
                TextInput::make('saldo_inicial')
                    ->required()
                    ->numeric(),
                TextInput::make('total_entradas')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_saidas')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('saldo_final')
                    ->numeric(),
                Select::make('status')
                    ->options(['aberto' => 'Aberto', 'fechado' => 'Fechado'])
                    ->default('aberto')
                    ->required(),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
