<?php

namespace App\Filament\Resources\Adicionals;

use App\Filament\Resources\Adicionals\Pages\CreateAdicional;
use App\Filament\Resources\Adicionals\Pages\EditAdicional;
use App\Filament\Resources\Adicionals\Pages\ListAdicionals;
use App\Filament\Resources\Adicionals\Schemas\AdicionalForm;
use App\Filament\Resources\Adicionals\Tables\AdicionalsTable;
use App\Models\Adicional;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdicionalResource extends Resource
{
    protected static ?string $model = Adicional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?string $pluralModelLabel = 'Adicionais';
    
    protected static ?string $recordTitleAttribute = 'descricao';

    public static function form(Schema $schema): Schema
    {
        return AdicionalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdicionalsTable::configure($table);
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
            'index' => ListAdicionals::route('/'),
            'create' => CreateAdicional::route('/create'),
            'edit' => EditAdicional::route('/{record}/edit'),
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
