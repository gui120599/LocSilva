<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = "tipos_documentos";
    
    protected $fillable = [
        'nome',
        'descricao'
    ];

    /**
     * Get the arquivos clientes for the tipo documento.
     */
    public function arquivosClientes()
    {
        return $this->hasMany(ArquivoCliente::class);
    }
}
