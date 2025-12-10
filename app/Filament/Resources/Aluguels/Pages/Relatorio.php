<?php

namespace App\Filament\Resources\Aluguels\Pages;

use App\Filament\Resources\Aluguels\AluguelResource;
use App\Models\Carreta;
use App\Models\Cliente;
use Filament\Resources\Pages\Page;

class Relatorio extends Page
{
    protected static string $resource = AluguelResource::class;

    public $clientes;

    public $carretas;

    protected string $view = 'filament.resources.aluguels.pages.relatorio';

        public function mount()
    {
        $this->clientes = Cliente::orderBy('nome')->get();
        $this->carretas = Carreta::orderBy('identificacao')->get();
    }
}
