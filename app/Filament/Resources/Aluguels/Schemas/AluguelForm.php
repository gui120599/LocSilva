<?php

namespace App\Filament\Resources\Aluguels\Schemas;

use App\Filament\Tables\CarretasTable;
use App\Models\Carreta;
use App\Services\IBGEServices;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\ImageFile;
use function PHPUnit\Framework\matches;

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
            ->columns(1)
            ->components([
                Wizard::make([
                    Step::make('Cliente')
                        ->schema([
                            Section::make()
                                ->description('Selecione o cliente para o aluguel')
                                ->icon('heroicon-s-user-group')
                                ->columns(1)
                                ->columnSpan(1)
                                ->schema([
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
                                        ->preload()
                                        ->required(),
                                ]),
                        ]),
                    Step::make('Carreta/Reboque')
                        ->schema([
                            Section::make()
                                ->description('Selecione a Carreta/Reboque para o aluguel')
                                ->icon('heroicon-s-document-text')
                                ->columns(2)
                                ->columnSpan(1)
                                ->schema([
                                    ModalTableSelect::make('carreta_id')
                                        ->relationship(
                                            'carreta',
                                            'identificacao'
                                        )
                                        ->label('Carreta/Reboque disponíveis')
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, callable $set, $state) {
                                            $carreta = Carreta::find($state);
                                            if ($carreta) {
                                                $valorFormatado = number_format((float) $carreta->valor_diaria, 2, ',', '.');
                                                $set('valor_diaria', $valorFormatado);
                                                $set('carreta.foto', $carreta->foto);
                                                $set('carreta.identificacao', $carreta->identificacao);
                                                $set('carreta.status', $carreta->status);
                                            }
                                        })
                                        ->tableConfiguration(CarretasTable::class),
                                    Section::make()
                                        ->description('Detalhes da Carreta/Reboque')
                                        ->schema([
                                            TextEntry::make('carreta.identificacao')
                                                ->label('Nº de Identificação'),
                                            TextEntry::make('carreta.status')
                                                ->label('Status da Carreta/Reboque')
                                                ->badge()
                                                ->color(fn(string $state): string => match ($state) {
                                                    'disponivel' => 'success',
                                                    'em_manutencao' => 'warning',
                                                    'alugada' => 'danger',
                                                    default => 'secondary',
                                                }),
                                            ImageEntry::make('carreta.foto')
                                                ->label('Imagem da Carreta/Reboque')
                                                ->disk('public'),
                                        ])
                                ]),
                        ]),
                    Step::make('Datas')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('data_retirada')
                                ->label('Data de Retirada')
                                ->default(now())
                                ->required(),
                            DatePicker::make('data_devolucao_prevista')
                                ->label('Data de Prevista para Devolução')
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $dataRetirada = $get('data_retirada');
                                    $valorDiaria = floatval($get('valor_diaria') ?? 0);

                                    if ($dataRetirada && $state) {
                                        $inicio = Carbon::parse($dataRetirada);
                                        $fim = Carbon::parse($state);

                                        // diferença em dias (se for 0 ou negativo, considera 1 diária)
                                        $dias = $inicio->diffInDays($fim);
                                        $diasValidos = $dias > 0 ? $dias : 1;

                                        $set('quantidade_diarias', $diasValidos);

                                        // 1. Calcule o valor total
                                        $valorTotal = $valorDiaria * $diasValidos;

                                        // 2. Formate o valor para string com separadores brasileiros
                                        $valorFormatado = number_format((float) $valorTotal, 2, ',', '.'); // Ex: '80.000,00'
                        
                                        // 3. Defina o estado com a string formatada
                                        $set('valor_total', $valorFormatado);
                                    } else {
                                        // se algum dos dois campos estiver vazio, zera quantidade/total (opcional)
                                        $set('quantidade_diarias', null);
                                        $set('valor_total', '0,00');
                                    }
                                })
                                ->required(),
                        ]),
                    Step::make('Valores')
                        ->columns(3)
                        ->schema([
                            TextInput::make('quantidade_diarias')
                                ->readOnly()
                                ->required(),
                            TextInput::make('valor_diaria')
                                ->live()
                                ->required()
                                ->prefix('R$')
                                ->mask(RawJs::make(<<<'JS'
                                            $money($input, ',', '.', 2)
                                        JS))
                                ->dehydrateStateUsing(function ($state) {
                                    // Remove formatação antes de salvar
                                    if (!$state)
                                        return 0;

                                    // Remove R$, pontos e converte vírgula em ponto
                                    $value = str_replace(['R$', '.', ' '], '', $state);
                                    $value = str_replace(',', '.', $value);

                                    return (float) $value;
                                })
                                ->formatStateUsing(function ($state) {
                                    // Formata para exibição
                                    if (!$state)
                                        return '0,00';

                                    return number_format((float) $state, 2, ',', '.');
                                })
                                ->placeholder('0,00')
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $quantidadeDiarias = intval($get('quantidade_diarias') ?? 0);

                                    if ($quantidadeDiarias > 0) {
                                        // 1. Calcule o valor total
                                        $valorTotal = floatval($state) * $quantidadeDiarias;

                                        // 2. Formate o valor para string com separadores brasileiros
                                        $valorFormatado = number_format((float) $valorTotal, 2, ',', '.'); // Ex: '80.000,00'
                        
                                        // 3. Defina o estado com a string formatada
                                        $set('valor_total', $valorFormatado);
                                        if (floatval($get('valor_pago')) > 0) {

                                            $saldo = $valorTotal - floatval($get('valor_pago'));

                                            // Formata saldo
                                            $saldoFormatado = number_format((float) $saldo, 2, ',', '.');

                                            // Seta o saldo
                                            $set('valor_saldo', $saldoFormatado);
                                        }

                                    } else {
                                        // se quantidade de diárias for zero, zera total (opcional)
                                        $set('valor_total', '0,00');
                                    }
                                }),
                            TextInput::make('valor_total')
                                ->readOnly()
                                ->required()
                                ->prefix('R$')
                                ->mask(RawJs::make(<<<'JS'
                                            $money($input, ',', '.', 2)
                                        JS))
                                ->dehydrateStateUsing(function ($state) {
                                    // Remove formatação antes de salvar
                                    if (!$state)
                                        return 0;

                                    // Remove R$, pontos e converte vírgula em ponto
                                    $value = str_replace(['R$', '.', ' '], '', $state);
                                    $value = str_replace(',', '.', $value);

                                    return (float) $value;
                                })
                                ->formatStateUsing(function ($state) {
                                    // Formata para exibição
                                    if (!$state)
                                        return '0,00';

                                    return number_format((float) $state, 2, ',', '.');
                                })
                                ->placeholder('0,00'),
                            TextInput::make('valor_pago')
                                ->live()
                                ->required()
                                ->prefix('R$')
                                ->mask(RawJs::make(<<<'JS'
                                            $money($input, ',', '.', 2)
                                        JS))
                                ->dehydrateStateUsing(function ($state) {
                                    // Remove formatação antes de salvar
                                    if (!$state)
                                        return 0;

                                    // Remove R$, pontos e converte vírgula em ponto
                                    $value = str_replace(['R$', '.', ' '], '', $state);
                                    $value = str_replace(',', '.', $value);

                                    return (float) $value;
                                })
                                ->formatStateUsing(function ($state) {
                                    // Formata para exibição
                                    if (!$state)
                                        return '0,00';

                                    return number_format((float) $state, 2, ',', '.');
                                })
                                ->placeholder('0,00')
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $valorTotalStr = $get('valor_total') ?? '0,00';
                                    // Remove formatação
                                    $valorTotal = floatval(str_replace(',', '.', $valorTotalStr));

                                    // Remove formatação do valor pago
                                    $valorPago = floatval(str_replace(',', '.', $state));

                                    // Calcula saldo
                                    $saldo = $valorTotal - $valorPago;

                                    // Formata saldo
                                    $saldoFormatado = number_format((float) $saldo, 2, ',', '.');

                                    // Seta o saldo
                                    $set('valor_saldo', $saldoFormatado);
                                }),
                            TextInput::make('valor_saldo')
                                ->required()
                                ->prefix('R$')
                                ->mask(RawJs::make(<<<'JS'
                                            $money($input, ',', '.', 2)
                                        JS))
                                ->dehydrateStateUsing(function ($state) {
                                    // Remove formatação antes de salvar
                                    if (!$state)
                                        return 0;

                                    // Remove R$, pontos e converte vírgula em ponto
                                    $value = str_replace(['R$', '.', ' '], '', $state);
                                    $value = str_replace(',', '.', $value);

                                    return (float) $value;
                                })
                                ->formatStateUsing(function ($state) {
                                    // Formata para exibição
                                    if (!$state)
                                        return '0,00';

                                    return number_format((float) $state, 2, ',', '.');
                                })
                                ->placeholder('0,00'),
                        ])

                ]),
                Section::make()
                    ->description('Observações do Aluguel')
                    ->icon('heroicon-s-chat-bubble-bottom-center-text')
                    ->collapsed(fn(string $operation): bool => $operation === 'create')
                    ->schema([
                        Select::make('status')
                            ->hidden()
                            ->options(['ativo' => 'Ativo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
                            ->default('ativo')
                            ->required(),
                        Textarea::make('observacoes')
                            ->columnSpanFull(),
                    ])

            ]);
    }
}
