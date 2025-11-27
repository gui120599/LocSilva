<?php

namespace App\Filament\Resources\MovimentoCaixas;

use App\Filament\Resources\MovimentoCaixas\Pages\ManageMovimentoCaixas;
use App\Models\MovimentoCaixa;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimentoCaixaResource extends Resource
{
    protected static ?string $model = MovimentoCaixa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'descricao';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('caixa_id')
                    ->relationship('caixa', 'id'),
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
                TextInput::make('valor_pago_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_recebido_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_acrescimo_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_desconto_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('troco_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_total_movimento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('caixa.id')
                    ->label('Caixa')
                    ->placeholder('-'),
                TextEntry::make('aluguel.id')
                    ->label('Aluguel')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('descricao')
                    ->placeholder('-'),
                TextEntry::make('tipo')
                    ->badge(),
                TextEntry::make('metodoPagamento.id')
                    ->label('Metodo pagamento'),
                TextEntry::make('cartao_pagamento_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('autorizacao')
                    ->placeholder('-'),
                TextEntry::make('valor_pago_movimento')
                    ->numeric(),
                TextEntry::make('valor_recebido_movimento')
                    ->numeric(),
                TextEntry::make('valor_acrescimo_movimento')
                    ->numeric(),
                TextEntry::make('valor_desconto_movimento')
                    ->numeric(),
                TextEntry::make('troco_movimento')
                    ->numeric(),
                TextEntry::make('valor_total_movimento')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (MovimentoCaixa $record): bool => $record->trashed()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMovimentoCaixas::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
