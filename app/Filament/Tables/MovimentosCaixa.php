<?php

namespace App\Filament\Tables;

use App\Models\MovimentoCaixa;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovimentosCaixa
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => MovimentoCaixa::query())
            ->columns([
                TextColumn::make('caixa.id')
                    ->searchable(),
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
                TextColumn::make('valor_pago_movimento')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_recebido_movimento')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_acrescimo_movimento')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_desconto_movimento')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('troco_movimento')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_total_movimento')
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
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
