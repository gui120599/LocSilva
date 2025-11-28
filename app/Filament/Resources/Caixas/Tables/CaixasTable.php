<?php

namespace App\Filament\Resources\Caixas\Tables;

use App\Filament\Tables\MovimentosCaixa;
use App\Models\Caixa;
use App\Models\MovimentoCaixa;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\HasMaxWidth;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions as TableActions;
use App\Filament\Resources\MovimentoCaixas\MovimentoCaixaResource;

class CaixasTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('data_abertura')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('data_fechamento')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
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
            ->recordActions([
                EditAction::make(),

                Action::make('fechar')
                    ->label('Fechar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Fechar Caixa')
                    ->modalDescription('Deseja fechar o caixa e imprimir o relatório de fechamento?')
                    ->modalIcon('heroicon-o-lock-closed')
                    ->form([
                        Textarea::make('observacoes')
                            ->label('Observações (opcional)')
                            ->placeholder('Adicione observações sobre o fechamento do caixa...')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Caixa $record, array $data) {
                        if (!$record->isAberto()) {
                            Notification::make()
                                ->warning()
                                ->title('Atenção')
                                ->body('Este caixa já está fechado.')
                                ->send();

                            return;
                        }

                        if (!empty($data['observacoes'])) {
                            $record->observacoes = $data['observacoes'];
                            $record->save();
                        }

                        $record->fechar();

                        Notification::make()
                            ->success()
                            ->title('Caixa Fechado')
                            ->body("Caixa #{$record->id} foi fechado com sucesso! Abrindo relatório para impressão...")
                            ->send();

                        // Abre em nova aba para impressão
                        //return redirect()->to(route('print-caixa', ['id' => $record->id]));
                    })
                    ->visible(fn(Caixa $record) => $record->isAberto()),

                Action::make('print')
                    ->label('Relatório')
                    ->icon('heroicon-s-printer')
                    ->color('primary')
                    // A CHAVE: Passar o registro ($record) para a rota dentro do closure
                    ->url(fn($record): string => route('print-caixa', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                // REMOVIDO: requiresConfirmation(true)
                Action::make('gerenciarMovimentos')
                ->icon('heroicon-o-list-bullet')
                    ->label('Movimentos')
                    ->modalHeading('Movimentos da Sessão')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->form([
                        RepeatableEntry::make('movimentos')
                            ->table([
                                TableColumn::make('Descricao'),
                                TableColumn::make('Valor Total Movimento'),
                                TableColumn::make('created_at'),
                            ])
                            ->schema([
                                TextEntry::make('descricao'),
                                TextEntry::make('valor_total_movimento')->money('BRL'),
                                TextEntry::make('created_at')->dateTime(),
                            ]),


                        Section::make('Adicionar Movimento')
                            ->schema([
                                Select::make('movimentos_selecionados')
                                    ->multiple()
                                    ->searchable()
                                    ->options(
                                        MovimentoCaixa::where('caixa_id', null)->get()
                                            ->mapWithKeys(fn($value) => [
                                                $value->id =>
                                                    "{$value->tipo} - {$value->descricao} - R$ "
                                                    . number_format($value->valor_total_movimento, 2, ',', '.')
                                                    . " - Aluguel: {$value->aluguel_id} - Cliente: "
                                                    . optional(optional($value->aluguel)->cliente)->nome

                                            ])
                                    )

                            ])
                    ])
                    ->action(function ($record, array $data) {
                        if (!empty($data['movimentos_selecionados'])) {
                            MovimentoCaixa::whereIn('id', $data['movimentos_selecionados'])
                                ->update(['caixa_id' => $record->id]);

                            Notification::make()
                                ->title('Movimentos associados com sucesso!')
                                ->success()
                                ->send();
                        }
                    }),


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }
}
