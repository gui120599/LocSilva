<?php

namespace App\Filament\Resources\Aluguels\Schemas;

use App\Filament\Tables\CarretasTable;
use App\Models\Carreta;
use App\Services\IBGEServices;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\ImageFile;

class AluguelForm
{
    public static function getCleanOptionString(Model $model): string
    {
        return new HtmlString(
            view('filament.components.select-user-results')
                ->with('name', $model?->identificacao)
                ->with('email', $model?->valor_diaria)
                ->with('image', $model?->foto)
                ->render()
        );
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cliente_id')->suffixIcon('heroicon-o-user-group')
                    ->relationship('cliente', 'nome')
                    ->createOptionForm([
                        Section::make()
                            ->description('Dados do cliente')
                            ->icon('heroicon-s-user-circle')
                            ->columns(4)
                            ->schema([
                                Section::make()
                                    ->columnSpan(3)
                                    ->schema([
                                        TextInput::make('nome')
                                            ->autocomplete(false)
                                            ->columnSpan(3)
                                            ->required(),
                                        TextInput::make('cpf_cnpj')
                                            ->required()
                                            ->label('CPF/CNPJ')
                                            ->autocomplete(false)
                                            ->dehydrateStateUsing(fn(string $state) => preg_replace("/\D/", "", $state))
                                            ->mask(RawJs::make(<<<'JS'
                                        $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                        JS
                                            ))
                                            ->disabled(fn(string $operation): bool => $operation === 'edit')
                                            ->rules([ //Não funciona o unique() pq ele verifica com a mask e na hora que salva no banco ele salva sem a mask
                                                'required',
                                                'cpf_ou_cnpj',
                                                fn($get, $record) => function ($attribute, $value, $fail) use ($record) {
                                                    // Remove formatação
                                                    $cpfCnpj = preg_replace("/\D/", "", $value);

                                                    // Verifica se já existe
                                                    $exists = \App\Models\Cliente::where('cpf_cnpj', $cpfCnpj)
                                                        ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                                        ->exists();

                                                    if ($exists) {
                                                        $fail('Este CPF/CNPJ já está cadastrado.');
                                                    }
                                                },
                                            ])
                                            ->columnSpan(2),
                                        DatePicker::make('data_nascimento'),
                                    ])
                                    ->columns(3),
                                Section::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        FileUpload::make('foto')
                                    ]),
                            ]),
                        Section::make()
                            ->description('Contato do cliente')
                            ->icon('heroicon-s-phone')
                            ->columns(2)
                            ->schema([
                                TextInput::make('telefone')
                                    ->dehydrateStateUsing(fn(string $state) => preg_replace("/\D/", "", $state))
                                    ->mask('(99)9 9999-9999')
                                    ->tel()
                                    ->required(),
                                TextInput::make('email')
                                    ->email(),
                            ]),
                        Section::make()
                            ->description('Endereço do cliente')
                            ->icon('heroicon-s-map-pin')
                            ->columns(6)
                            ->schema([
                                TextInput::make('cep')
                                    ->mask('99999-999')
                                    ->live() // Garante que as mudanças no campo disparem a ação.
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Limpa o CEP para conter apenas números.
                                        $cepLimpo = preg_replace('/[^0-9]/', '', $state);
                                        if (strlen($cepLimpo) === 8) {

                                            $dadosEndereco = IBGEServices::buscaCep($cepLimpo);

                                            if ($dadosEndereco) {
                                                $set('endereco', $dadosEndereco['logradouro'] ?? '');
                                                $set('bairro', $dadosEndereco['bairro'] ?? '');
                                                $set('estado', $dadosEndereco['uf'] ?? '');
                                                $set('cidade', $dadosEndereco['localidade'] ?? '');
                                            } else {
                                                // Opcional: Limpar campos se a busca falhar
                                                $set('endereco', '');
                                                $set('bairro', '');
                                                $set('estado', '');
                                                $set('cidade', '');
                                                // Opcional: Adicionar uma notificação de erro
                                            }

                                        }
                                    }),
                                TextInput::make('endereco')
                                    ->columnSpan(3)
                                    ->label('Logradouro'),
                                TextInput::make('bairro')
                                    ->columnSpan(2),
                                Select::make('estado')
                                    ->live()
                                    ->preload(false)
                                    ->options(IBGEServices::ufs())
                                    ->searchable()
                                    ->columnSpan(3),
                                Select::make('cidade')
                                    ->label('Cidade')
                                    ->preload()
                                    ->searchable()
                                    ->options(function (Get $get) {
                                        // Pega a sigla (valor) selecionada no campo 'estado'
                                        $uf = $get('estado');
                                        // Se o estado não estiver selecionado, não retorna nenhuma opção
                                        if (empty($uf)) {
                                            return [];
                                        }
                                        // Chama o novo método no seu serviço para buscar as cidades da UF
                                        return IBGEServices::cidadesPorUf($uf);
                                    })
                                    ->columnSpan(3),
                            ]),
                        Section::make()
                            ->description('Observações do cliente')
                            ->icon('heroicon-s-chat-bubble-bottom-center-text')
                            ->columns(1)
                            ->schema([
                                Textarea::make('observacoes'),
                            ])
                    ])
                    ->searchable()
                    ->required(),
                ModalTableSelect::make('carreta_id')
                    ->relationship('carreta', 'identificacao')
                    ->label('Carreta/Reboque')
                    ->live()
                    ->afterStateUpdated(function (Get $get, callable $set, $state) {
                        $carreta = Carreta::find($state);
                        if ($carreta) {
                            $set('valor_diaria', $carreta->valor_diaria);
                            $set('quantidade_diarias', 1);
                            $set('valor_total', $carreta->valor_diaria);
                            $set('valor_saldo', $carreta->valor_diaria);
                            $set('carreta.foto', $carreta->foto);
                        }
                    })
                    ->tableConfiguration(CarretasTable::class),
                ImageEntry::make('carreta.foto')
                    ->label('Imagem da Carreta/Reboque')
                    ->circular()
                    ->disk('public'),
                DatePicker::make('data_retirada')
                    ->required(),
                DatePicker::make('data_devolucao_prevista')
                    ->required(),
                DatePicker::make('data_devolucao_real'),
                TextInput::make('valor_diaria')
                    ->required()
                    ->numeric(),
                TextInput::make('quantidade_diarias')
                    ->required(),
                TextInput::make('valor_total')
                    ->required()
                    ->numeric(),
                TextInput::make('valor_pago')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('valor_saldo')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['ativo' => 'Ativo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
                    ->default('ativo')
                    ->required(),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
