<?php

namespace App\Filament\Resources\Aluguels;

use App\Filament\Resources\Aluguels\Pages\CreateAluguel;
use App\Filament\Resources\Aluguels\Pages\EditAluguel;
use App\Filament\Resources\Aluguels\Pages\ListAluguels;
use App\Filament\Resources\Aluguels\Schemas\AluguelForm;
use App\Filament\Resources\Aluguels\Tables\AluguelsTable;
use App\Models\Aluguel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AluguelResource extends Resource
{
    protected static ?string $model = Aluguel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'data_retirada';

    public static function form(Schema $schema): Schema
    {
        return AluguelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AluguelsTable::configure($table);
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
            'index' => ListAluguels::route('/'),
            'create' => CreateAluguel::route('/create'),
            'edit' => EditAluguel::route('/{record}/edit'),
        ];
    }
}
