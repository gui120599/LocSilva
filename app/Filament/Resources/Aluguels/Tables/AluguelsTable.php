<?php

namespace App\Filament\Resources\Aluguels\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AluguelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('status')
                    ->tooltip(fn(string $state): string => match ($state) {
                        'ativo' => 'ATIVO',
                        'finalizado' => 'FINALIZADO',
                        'receber' => 'A RECEBER',
                        'cancelado' => 'CANCELADO',
                        default => strtoupper($state),
                    })
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'ativo' => Heroicon::OutlinedTruck,
                        'finalizado' => Heroicon::OutlinedCheckCircle,
                        'receber' => Heroicon::OutlinedWrenchScrewdriver,
                        'cancelado' => Heroicon::OutlinedXCircle,
                    })
                    ->colors([
                        'success' => 'ativo',
                        'info' => 'finalizado',
                        'warning' => 'receber',
                        'danger' => 'cancelado',
                    ]),
                TextColumn::make('cliente.nome')
                    ->sortable(),
                TextColumn::make('carreta.identificacao')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('data_retirada')
                    ->label('Retirada')
                    ->color('success')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('data_devolucao_prevista')
                    ->label('Devolução Prevista')
                    ->color('warning')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('data_devolucao_real')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('quantidade_diarias')
                    ->label('Diárias')
                    ->description('Dias')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valor_diaria')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valor_total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valor_pago')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valor_saldo')
                    ->money('BRL')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->default('ativo')
                    ->options([
                        'ativo' => 'Ativo',
                        'finalizado' => 'Finalizado',
                        'receber' => 'A Receber',
                        'cancelado' => 'Cancelado',
                    ])
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'ativo'),
                Action::make('finalizar')
                    ->label('Finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'ativo')
                    ->requiresConfirmation()
                    ->modalHeading('Finalizar Aluguel')
                    ->modalDescription('Informe a data de devolução e o valor do pagamento final.')
                    ->form([
                        DatePicker::make('data_devolucao_real')
                            ->label('Data de Devolução')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        TextInput::make('valor_pagamento')
                            ->label('Pagamento Final')
                            ->numeric()
                            ->prefix('R$')
                            ->default(fn($record) => $record->valor_saldo)
                            ->helperText(fn($record) => "Saldo restante: R$ " . number_format($record->valor_saldo, 2, ',', '.')),
                    ])
                    ->action(function ($record, array $data) {
                        // Atualiza o aluguel
                        $record->update([
                            'data_devolucao_real' => $data['data_devolucao_real'],
                            'status' => 'finalizado',
                            'valor_pago' => $record->valor_pago + ($data['valor_pagamento'] ?? 0),
                            'valor_saldo' => max(0, $record->valor_total - ($record->valor_pago + ($data['valor_pagamento'] ?? 0))),
                        ]);

                        // O Observer vai liberar a carreta automaticamente
            
                        Notification::make()
                            ->success()
                            ->title('Aluguel finalizado com sucesso!')
                            ->body("Carreta {$record->carreta->identificacao} foi liberada.")
                            ->send();
                    }),
                // Action para Cancelar
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'ativo')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Aluguel')
                    ->modalDescription('Tem certeza que deseja cancelar este aluguel?')
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo do Cancelamento')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->cancelar($data['motivo']);

                        // O Observer vai liberar a carreta automaticamente
            
                        Notification::make()
                            ->success()
                            ->title('Aluguel cancelado')
                            ->body("Carreta {$record->carreta->identificacao} foi liberada.")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                /*BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),*/
            ]);
    }
}
