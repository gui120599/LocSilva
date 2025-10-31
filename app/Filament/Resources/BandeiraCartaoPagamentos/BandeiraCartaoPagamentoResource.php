<?php

namespace App\Filament\Resources\BandeiraCartaoPagamentos;

use App\Filament\Resources\BandeiraCartaoPagamentos\Pages\CreateBandeiraCartaoPagamento;
use App\Filament\Resources\BandeiraCartaoPagamentos\Pages\EditBandeiraCartaoPagamento;
use App\Filament\Resources\BandeiraCartaoPagamentos\Pages\ListBandeiraCartaoPagamentos;
use App\Filament\Resources\BandeiraCartaoPagamentos\Schemas\BandeiraCartaoPagamentoForm;
use App\Filament\Resources\BandeiraCartaoPagamentos\Tables\BandeiraCartaoPagamentosTable;
use App\Models\BandeiraCartaoPagamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BandeiraCartaoPagamentoResource extends Resource
{
    protected static ?string $model = BandeiraCartaoPagamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'bandeira';

    protected static ?string $modelLabel = 'Bandeira de Cartão de Pagamento';

    protected static ?string $pluralModelLabel = 'Bandeiras de Cartão de Pagamento';

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return BandeiraCartaoPagamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BandeiraCartaoPagamentosTable::configure($table);
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
            'index' => ListBandeiraCartaoPagamentos::route('/'),
            'create' => CreateBandeiraCartaoPagamento::route('/create'),
            'edit' => EditBandeiraCartaoPagamento::route('/{record}/edit'),
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
