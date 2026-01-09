<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');
        
        // Cria a nova pasta se não existir
        if (!$disk->exists('arquivos_clientes')) {
            $disk->makeDirectory('arquivos_clientes');
        }

        // Migra documentos (RG/CNH) dos clientes
        DB::table('clientes')
            ->whereNotNull('documento')
            ->where('documento', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($clientes) use ($disk) {
                foreach ($clientes as $cliente) {
                    $caminhoAntigo = $cliente->documento;
                    $nomeArquivo = basename($caminhoAntigo);
                    $novoCaminho = 'arquivos_clientes/' . $nomeArquivo;

                    // Move o arquivo se existir
                    if ($disk->exists($caminhoAntigo)) {
                        // Se já existe arquivo com mesmo nome na pasta destino, adiciona timestamp
                        if ($disk->exists($novoCaminho)) {
                            $info = pathinfo($nomeArquivo);
                            $nomeArquivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                            $novoCaminho = 'arquivos_clientes/' . $nomeArquivo;
                        }
                        
                        $disk->move($caminhoAntigo, $novoCaminho);
                    }

                    DB::table('arquivos_clientes')->insert([
                        'cliente_id' => $cliente->id,
                        'tipo_documento_id' => 1,
                        'url_documento' => $novoCaminho,
                        'data_validade_documento' => null,
                        'observacoes_documento' => 'Migrado automaticamente - Documento',
                        'created_at' => $cliente->created_at ?? now(),
                        'updated_at' => $cliente->updated_at ?? now(),
                    ]);
                }
            });

        // Migra notas promissórias dos clientes
        DB::table('clientes')
            ->whereNotNull('nota_promissoria')
            ->where('nota_promissoria', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($clientes) use ($disk) {
                foreach ($clientes as $cliente) {
                    $caminhoAntigo = $cliente->nota_promissoria;
                    $nomeArquivo = basename($caminhoAntigo);
                    $novoCaminho = 'arquivos_clientes/' . $nomeArquivo;

                    // Move o arquivo se existir
                    if ($disk->exists($caminhoAntigo)) {
                        // Se já existe arquivo com mesmo nome na pasta destino, adiciona timestamp
                        if ($disk->exists($novoCaminho)) {
                            $info = pathinfo($nomeArquivo);
                            $nomeArquivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                            $novoCaminho = 'arquivos_clientes/' . $nomeArquivo;
                        }
                        
                        $disk->move($caminhoAntigo, $novoCaminho);
                    }

                    DB::table('arquivos_clientes')->insert([
                        'cliente_id' => $cliente->id,
                        'tipo_documento_id' => 1,
                        'url_documento' => $novoCaminho,
                        'data_validade_documento' => null,
                        'observacoes_documento' => 'Migrado automaticamente - Nota Promissória',
                        'created_at' => $cliente->created_at ?? now(),
                        'updated_at' => $cliente->updated_at ?? now(),
                    ]);
                }
            });

        // Opcional: Remove as pastas antigas se estiverem vazias
        $this->limparPastasAntigas($disk);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $disk = Storage::disk('public');

        // Busca os registros migrados para restaurar os arquivos
        $arquivosMigrados = DB::table('arquivos_clientes')
            ->where('observacoes_documento', 'like', 'Migrado automaticamente%')
            ->get();

        foreach ($arquivosMigrados as $arquivo) {
            $caminhoAtual = $arquivo->url_documento;
            
            // Determina a pasta de origem baseado na observação
            if (str_contains($arquivo->observacoes_documento, 'Nota Promissória')) {
                $pastaOrigem = 'promissorias_clientes';
            } else {
                $pastaOrigem = 'documentos_clientes';
            }

            $nomeArquivo = basename($caminhoAtual);
            $caminhoOriginal = $pastaOrigem . '/' . $nomeArquivo;

            // Move de volta se o arquivo existir
            if ($disk->exists($caminhoAtual)) {
                // Recria a pasta original se não existir
                if (!$disk->exists($pastaOrigem)) {
                    $disk->makeDirectory($pastaOrigem);
                }
                
                $disk->move($caminhoAtual, $caminhoOriginal);
            }
        }

        // Remove os registros do banco
        DB::table('arquivos_clientes')
            ->where('observacoes_documento', 'like', 'Migrado automaticamente%')
            ->delete();
    }

    /**
     * Limpa as pastas antigas se estiverem vazias
     */
    private function limparPastasAntigas($disk): void
    {
        $pastasAntigas = ['documentos_clientes', 'promissorias_clientes'];

        foreach ($pastasAntigas as $pasta) {
            if ($disk->exists($pasta)) {
                $arquivos = $disk->files($pasta);
                
                // Se a pasta estiver vazia, remove
                if (empty($arquivos)) {
                    $disk->deleteDirectory($pasta);
                }
            }
        }
    }
};