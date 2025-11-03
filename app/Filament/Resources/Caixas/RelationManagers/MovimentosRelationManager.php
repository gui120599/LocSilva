<?php

namespace App\Filament\Resources\Caixas\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimentosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimentos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('aluguel_id')
                    ->relationship('aluguel', 'id'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('descricao'),
                Select::make('tipo')
                    ->options(['entrada' => 'Entrada', 'saida' => 'Saida'])
                    ->default('entrada')
                    ->required(),
                Select::make('metodo_pagamento_id')
                    ->relationship('metodoPagamento', 'id')
                    ->required(),
                TextInput::make('cartao_pagamento_id')
                    ->numeric(),
                TextInput::make('autorizacao'),
                TextInput::make('valor_pago')
                    ->required()
                    ->numeric(),
                TextInput::make('valor_recebido')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_acrescimo')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_desconto')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('troco')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('aluguel.id')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('descricao')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->badge(),
                TextColumn::make('metodoPagamento.id')
                    ->searchable(),
                TextColumn::make('cartao_pagamento_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('autorizacao')
                    ->searchable(),
                TextColumn::make('valor_pago')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_recebido')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_acrescimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_desconto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('troco')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
