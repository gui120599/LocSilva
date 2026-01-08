<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArquivoCliente extends Model
{
    protected $table = "arquivos_clientes";

    protected $fillable = [
        'cliente_id',
        'tipo_documento_id',
        'url_documento',
        'data_validade_documento',
        'status_documento',
        'observacoes_documento'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }
}
