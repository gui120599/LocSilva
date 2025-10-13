<?php

namespace App\Filament\Resources\Carretas;

use App\Filament\Resources\Carretas\Pages\CreateCarreta;
use App\Filament\Resources\Carretas\Pages\EditCarreta;
use App\Filament\Resources\Carretas\Pages\ListCarretas;
use App\Filament\Resources\Carretas\Schemas\CarretaForm;
use App\Filament\Resources\Carretas\Tables\CarretasTable;
use App\Models\Carreta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CarretaResource extends Resource
{
    protected static ?string $model = Carreta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'identificacao';

    public static function form(Schema $schema): Schema
    {
        return CarretaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarretasTable::configure($table);
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
            'index' => ListCarretas::route('/'),
            'create' => CreateCarreta::route('/create'),
            'edit' => EditCarreta::route('/{record}/edit'),
        ];
    }
}
