<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required(),
                TextInput::make('cpf_cnpj')
                    ->required(),
                TextInput::make('telefone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('endereco'),
                TextInput::make('cidade'),
                TextInput::make('estado'),
                TextInput::make('cep'),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
