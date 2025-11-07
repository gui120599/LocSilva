<?php

namespace App\Filament\Resources\Aluguels;

use App\Filament\Resources\Aluguels\Pages\CreateAluguel;
use App\Filament\Resources\Aluguels\Pages\EditAluguel;
use App\Filament\Resources\Aluguels\Pages\ListAluguels;
use App\Filament\Resources\Aluguels\Pages\ViewAluguel;
use App\Filament\Resources\Aluguels\Schemas\AluguelForm;
use App\Filament\Resources\Aluguels\Schemas\AluguelInfolist;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $pluralModelLabel = 'Alugueis';

    protected static ?string $recordTitleAttribute = 'id';

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
            'aluguel' => Pages\Aluguel::route('/{record}/recibo'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        // 1. Obtém a classe do modelo (static::getModel())
        $modelClass = static::getModel();

        // 2. Chama o método estático 'where' na classe do modelo,
        //    que retorna o Builder, e encadeia o método de instância 'count()'
        return $modelClass::whereIn('status', ['ativo', 'pendente'])->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        // 1. Obtém a classe do modelo (static::getModel())
        $modelClass = static::getModel();

        // 2. Chama o método estático 'where' na classe do modelo,
        //    que retorna o Builder, e encadeia o método de instância 'count()'
        return $modelClass::whereIn('status', ['ativo', 'pendente'])->count() >= 0 ? 'success' : null;
    }
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Alugueis ativos/pendentes';
    }


}
