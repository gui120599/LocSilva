<?php

namespace App\Filament\Resources\Caixas\Tables;

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
use Filament\Forms\Components\Select;
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
                Action::make('gerenciarMovimentos')
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
                                    ->label('Selecionar Movimentos')
                                    ->multiple()
                                    ->options(
                                        fn() =>
                                        MovimentoCaixa::whereNull('caixa_id')
                                            ->pluck('descricao', 'id')
                                    )
                                    ->searchable()
                                    ->preload(),
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



                Action::make('visualizar_movimentos')
                    ->label('Visualizar Movimentos de Caixa')
                    ->icon('heroicon-o-table-cells')
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalContent(function (array $arguments, $record, $livewire) {

                        // Query dos movimentos desta sessão/caixa
                        $query = $record->movimentos()->getQuery();

                        // Cria a instância da Table — ATENÇÃO: precisa de um nome (string)
                        $table = Table::make($livewire); // <-- corrija passando um nome
            
                        // Deixe o Resource aplicar suas colunas/filtros/padrões
                        $configuredTable = MovimentoCaixaResource::table($table);

                        // Aplica a query específica (movimentos da sessão corrente)
                        $configuredTable->query($query);

                        // Ajustes opcionais (paginação, quantidade por página)
                        $configuredTable->defaultSort('created_at', 'desc');

                        // Sobrescreve actions de linha para incluir 'Desassociar'
                       

                        // Renderiza passando o componente Livewire atual
                        return $configuredTable->render($livewire);
                    })



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
