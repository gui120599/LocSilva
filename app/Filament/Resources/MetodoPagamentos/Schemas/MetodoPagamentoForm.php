<?php

namespace App\Filament\Resources\MetodoPagamentos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MetodoPagamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required(),
                Select::make('taxa_tipo')
                    ->options(['N/A' => 'N/A', 'DESCONTAR' => 'Descontar', 'ACRESCENTAR' => 'Acrescentar'])
                    ->default('N/A')
                    ->required(),
                TextInput::make('taxa_percentual')
                    ->prefix('%')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->dehydrateStateUsing(function ($state) {
                        // Remove formatação antes de salvar
                        if (!$state)
                            return 0;

                        // Remove R$, pontos e converte vírgula em ponto
                        $value = str_replace(['%', '.', ' '], '', $state);
                        $value = str_replace(',', '.', $value);

                        return (float) $value;
                    }),
                Select::make('descricao_nfe')
                    ->options([
                        'cash' => 'Cash',
                        'cheque' => 'Cheque',
                        'creditCard' => 'Credit card',
                        'debitCard' => 'Debit card',
                        'storeCredict' => 'Store credict',
                        'foodVouchers' => 'Food vouchers',
                        'mealVouchers' => 'Meal vouchers',
                        'giftVouchers' => 'Gift vouchers',
                        'fuelVouchers' => 'Fuel vouchers',
                        'bankBill' => 'Bank bill',
                        'withoutPayment' => 'Without payment',
                        'InstantPayment' => 'Instant payment',
                        'others' => 'Others',
                    ]),
            ]);
    }
}
