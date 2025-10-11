<?php

namespace App\Filament\Resources\Caixas;

use App\Filament\Resources\Caixas\Pages\CreateCaixa;
use App\Filament\Resources\Caixas\Pages\EditCaixa;
use App\Filament\Resources\Caixas\Pages\ListCaixas;
use App\Filament\Resources\Caixas\Schemas\CaixaForm;
use App\Filament\Resources\Caixas\Tables\CaixasTable;
use App\Models\Caixa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CaixaResource extends Resource
{
    protected static ?string $model = Caixa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'data_abertura';

    public static function form(Schema $schema): Schema
    {
        return CaixaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CaixasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCaixas::route('/'),
            'create' => CreateCaixa::route('/create'),
            'edit' => EditCaixa::route('/{record}/edit'),
        ];
    }
}
