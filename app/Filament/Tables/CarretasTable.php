<?php

namespace App\Filament\Tables;

use App\Models\Carreta;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarretasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Imagem')
                    ->disk('public')
                    ->rounded(),
                TextColumn::make('identificacao')
                    ->label('Identificação')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('marca')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('modelo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ano')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('placa')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('capacidade_carga')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valor_diaria')
                    ->money('BRL', divideBy: 1)
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar')
                    ->toggleable(),
                IconColumn::make('status')
                    ->tooltip(fn(string $state): string => match ($state) {
                        'disponivel' => 'DISPONÍVEL',
                        'alugada' => 'ALUGADA',
                        'manutencao' => 'EM MANUTENÇÃO',
                        default => strtoupper($state),
                    })
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'disponivel' => Heroicon::OutlinedCheckCircle,
                        'alugada' => Heroicon::OutlinedTruck,
                        'manutencao' => Heroicon::OutlinedWrenchScrewdriver
                    })
                    ->colors([
                        'success' => 'disponivel',
                        'info' => 'alugada',
                        'warning' => 'manutencao',
                    ]),
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'disponivel' => 'Disponível',
                        'alugada' => 'Alugada',
                        'manutencao' => 'Em Manutenção',
                    ]),
                Filter::make('created_at')
                    ->form([
                        Select::make('created_at')
                            ->label('Criado em')
                            ->options([
                                'today' => 'Hoje',
                                'this_week' => 'Esta Semana',
                                'this_month' => 'Este Mês',
                                'this_year' => 'Este Ano',
                            ]),
                    ])
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
