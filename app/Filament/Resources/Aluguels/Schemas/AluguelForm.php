<?php

namespace App\Filament\Resources\Aluguels\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AluguelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cliente_id')
                    ->relationship('cliente', 'id')
                    ->required(),
                Select::make('carreta_id')
                    ->relationship('carreta', 'id')
                    ->required(),
                Select::make('caixa_id')
                    ->relationship('caixa', 'id'),
                DatePicker::make('data_retirada')
                    ->required(),
                DatePicker::make('data_devolucao_prevista')
                    ->required(),
                DatePicker::make('data_devolucao_real'),
                TextInput::make('valor_diaria')
                    ->required()
                    ->numeric(),
                TextInput::make('quantidade_diarias')
                    ->required()
                    ->numeric(),
                TextInput::make('valor_total')
                    ->required()
                    ->numeric(),
                TextInput::make('valor_pago')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_saldo')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['ativo' => 'Ativo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
                    ->default('ativo')
                    ->required(),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
