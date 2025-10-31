<?php

namespace App\Filament\Resources\BandeiraCartaoPagamentos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class BandeiraCartaoPagamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bandeira')
                    ->options([
                        'None' => 'None',
                        'Visa' => 'Visa',
                        'Mastercard' => 'Mastercard',
                        'AmericanExpress' => 'American express',
                        'Sorocred' => 'Sorocred',
                        'DinersClub' => 'Diners club',
                        'Elo' => 'Elo',
                        'Hipercard' => 'Hipercard',
                        'Aura' => 'Aura',
                        'Cabal' => 'Cabal',
                        'Alelo' => 'Alelo',
                        'BanesCard' => 'Banes card',
                        'CalCard' => 'Cal card',
                        'Credz' => 'Credz',
                        'Discover' => 'Discover',
                        'GoodCard' => 'Good card',
                        'GreenCard' => 'Green card',
                        'Hiper' => 'Hiper',
                        'JCB' => 'J c b',
                        'Mais' => 'Mais',
                        'MaxVan' => 'Max van',
                        'Policard' => 'Policard',
                        'RedeCompras' => 'Rede compras',
                        'Sodexo' => 'Sodexo',
                        'ValeCard' => 'Vale card',
                        'Verocheque' => 'Verocheque',
                        'VR' => 'V r',
                        'Ticket' => 'Ticket',
                        'Other' => 'Other',
                    ])
                    ->required(),
                TextInput::make('cnpj_crendeciador')
                    ->autocomplete(false)
                    ->dehydrateStateUsing(fn(string $state) => preg_replace("/\D/", "", $state))
                    ->mask('99.999.999/9999-99'),
            ]);
    }
}
