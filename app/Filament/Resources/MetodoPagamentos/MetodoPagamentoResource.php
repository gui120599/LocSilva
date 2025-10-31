<?php

namespace App\Filament\Resources\MetodoPagamentos;

use App\Filament\Resources\MetodoPagamentos\Pages\CreateMetodoPagamento;
use App\Filament\Resources\MetodoPagamentos\Pages\EditMetodoPagamento;
use App\Filament\Resources\MetodoPagamentos\Pages\ListMetodoPagamentos;
use App\Filament\Resources\MetodoPagamentos\Schemas\MetodoPagamentoForm;
use App\Filament\Resources\MetodoPagamentos\Tables\MetodoPagamentosTable;
use App\Models\MetodoPagamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MetodoPagamentoResource extends Resource
{
    protected static ?string $model = MetodoPagamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?string $modelLabel = 'Método de Pagamento';
    protected static ?string $pluralModelLabel = 'Métodos de Pagamento';

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return MetodoPagamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetodoPagamentosTable::configure($table);
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
            'index' => ListMetodoPagamentos::route('/'),
            'create' => CreateMetodoPagamento::route('/create'),
            'edit' => EditMetodoPagamento::route('/{record}/edit'),
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
