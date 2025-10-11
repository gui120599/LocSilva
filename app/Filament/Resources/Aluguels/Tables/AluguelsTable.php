<?php

namespace App\Filament\Resources\Aluguels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AluguelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('carreta.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('caixa.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('data_retirada')
                    ->date()
                    ->sortable(),
                TextColumn::make('data_devolucao_prevista')
                    ->date()
                    ->sortable(),
                TextColumn::make('data_devolucao_real')
                    ->date()
                    ->sortable(),
                TextColumn::make('valor_diaria')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantidade_diarias')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_pago')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_saldo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
