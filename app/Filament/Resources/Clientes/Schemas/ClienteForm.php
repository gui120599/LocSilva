<?php

namespace App\Filament\Resources\Clientes\Schemas;

use App\Models\Cliente;
use App\Models\TipoDocumento;
use App\Services\IBGEServices;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentPtbrFormFields\Document;
use PhpParser\Comment\Doc;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
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
                                Document::make('cpf_cnpj')
                                    ->dynamic()
                                    ->required()
                                    ->label('CPF/CNPJ')
                                    ->autocomplete(false)
                                    ->unique(table: Cliente::class, column: 'cpf_cnpj', ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Este CPF/CNPJ já está em uso.',
                                    ])
                                    ->disabled(fn(string $operation) => $operation === 'edit')
                                    ->columnSpan(2),

                                DatePicker::make('data_nascimento'),
                            ])
                            ->columns(3),
                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('foto')
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('fotos_clientes')
                                    ->label('Foto do Cliente'),
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
                    ->description('Documentos do Cliente')
                    ->icon('heroicon-s-paper-clip')
                    ->columns(2)
                    ->schema([
                        Repeater::make('arquivos')
                            ->collapsible()
                            ->columnSpanFull()
                            ->relationship()
                            ->columns(4)
                            ->itemLabel(function (array $state): ?string {
                                if (!isset($state['tipo_documento_id'])) {
                                    return 'Novo Documento';
                                }

                                $TipoDocumento = TipoDocumento::find($state['tipo_documento_id']);

                                return $TipoDocumento
                                    ? $TipoDocumento->nome
                                    : 'Documento';
                            })
                            ->schema([
                                Select::make('tipo_documento_id')
                                    ->disabled(fn(?Model $record): bool => $record !== null)
                                    ->dehydrated()
                                    ->reactive()
                                    ->options(TipoDocumento::query()->pluck('nome', 'id')),
                                DatePicker::make('data_validade_documento')
                                    ->label('Validade')
                                    ->disabled(fn(?Model $record): bool => $record !== null)
                                    ->dehydrated(),
                                FileUpload::make('url_documento')
                                    ->label('Arquivo')
                                    ->directory('arquivos_clientes')
                                    ->disk('public')
                                    ->downloadable()
                                    ->openable()
                                    ->maxSize(2048)
                                    ->hint('Máx. 2MB'),
                                Textarea::make('observacoes_documento')
                                    ->label('Observações')
                                    ->rows(3),
                            ]),
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
                            ->columnSpan(2)
                            ->label('Logradouro'),
                        TextInput::make('complemento_endereco')
                            ->columnSpan(3)
                            ->label('Complemento'),
                        TextInput::make('bairro')
                            ->columnSpan(2),
                        Select::make('estado')
                            ->live()
                            ->preload(false)
                            ->options(IBGEServices::ufs())
                            ->searchable()
                            ->columnSpan(2),
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
                            ->columnSpan(2),
                    ]),
                Section::make()
                    ->description('Observações do cliente')
                    ->icon('heroicon-s-chat-bubble-bottom-center-text')
                    ->columns(1)
                    ->schema([
                        Textarea::make('observacoes'),
                    ])
            ]);
    }
}
