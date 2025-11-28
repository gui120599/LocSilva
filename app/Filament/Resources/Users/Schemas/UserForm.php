<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    FileUpload::make('avatar_url')
                        ->disk('public')
                        ->directory('avatars')
                        ->label('Avatar')
                        ->avatar(),
                    Select::make('role_id')
                        ->label('Papel')
                        ->relationship('role', 'name')
                        ->required()
                        ->default(2),
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->unique()
                        ->required(),
                    TextInput::make('password')
                        ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                        ->dehydrated(fn(?string $state): bool => filled($state))
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->revealable()
                        ->password(),
                ])->columns(2)
            ])->columns(1);
    }
}
