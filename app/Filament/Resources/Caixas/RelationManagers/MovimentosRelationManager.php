<?php

namespace App\Filament\Resources\Caixas\RelationManagers;

use App\Models\MetodoPagamento;
use App\Models\User;
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
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
            ->recordTitleAttribute('descricao')
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
                CreateAction::make()
                    ->schema([
                        Section::make()
                        ->columns(4)
                            ->schema([
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->default(auth()->user()->id),
                                Select::make('tipo')
                                    ->hidden()
                                    ->options(['entrada' => 'Entrada', 'saida' => 'Saida'])
                                    ->default('entrada')
                                    ->required(),
                                ToggleButtons::make('metodo_pagamento_id')
                                    ->label('Método do Pagamento')
                                    ->columnSpanFull()
                                    ->options(
                                        function () {
                                            return MetodoPagamento::pluck('nome', 'id')->toArray();
                                        }
                                    )
                                    ->icons([
                                        1 => Heroicon::Banknotes,
                                        2 => Heroicon::CreditCard,
                                        3 => Heroicon::CreditCard,
                                        4 => Heroicon::QrCode
                                    ])
                                    ->default(1)
                                    ->grouped(),

                                Select::make('cartao_pagamento_id')
                                    ->relationship('bandeiraCartao', 'bandeira')
                                    // Tornar este campo visível APENAS se o método de pagamento for 'Cartão' (opcional)
                                    ->visible(function ($get) {
                                        // Pega o NOME do método de pagamento
                                        $metodoNome = $get('metodoPagamento.nome');

                                        // Se a relação não carregar o nome, use o ID e procure manualmente
                                        // Se a relação estiver bem definida, o código acima funciona
                                        return $metodoNome === 'Cartão';
                                    }),

                                TextInput::make('autorizacao')
                                    ->label('Nº de Autorização da Transação')
                                    // Define a visibilidade do campo 'autorizacao'
                                    ->visible(function ($get) {
                                        // Captura o nome do método de pagamento selecionado.
                                        // O '.nome' funciona porque o Select acima usa 'metodoPagamento.nome' na relação.
                                        $metodoNome = $get('metodoPagamento.nome');

                                        // Verifica se o nome está na lista de métodos que exigem autorização.
                                        return in_array($metodoNome, ['Cartão', 'Pix']);

                                        // Caso você precise usar o ID (mais seguro), use o ID:
                                        // $metodoId = $get('metodo_pagamento_id'); 
                                        // return in_array($metodoId, [1, 2]); // Ex: 1 para Cartão, 2 para Pix
                                    }),
                                TextInput::make('valor_recebido')
                                    ->required()
                                    ->numeric()
                                    ->default(0.0),
                                TextInput::make('valor_total')
                                ->readonly()
                                    ->required()
                                    ->numeric()
                                    ->default(0.0),
                            ]),
                    ]),
                AssociateAction::make()
                    ->label('Associar Movimento')
                    ->recordSelectSearchColumns(['descricao', 'id'])
                    ->multiple()
                    ->preloadRecordSelect(),
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
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
