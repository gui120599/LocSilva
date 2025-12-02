<?php

namespace App\Observers;

use App\Models\MovimentoCaixa;

class MovimentoCaixaObserver
{
    /**
     * Antes de criar um movimento de caixa
     */
    public function creating(): void
    {
        // Lógica a ser executada antes de criar um movimento de caixa 
    }


    /**
     * Após criar um movimento de caixa
     */
    public function created(MovimentoCaixa $movimento): void
    {
        if ($movimento->valor_total_movimento <= 0) {
            $movimento->delete();

            Log::info("Movimento de caixa com valor zero excluído após criação", [
                'movimento_id' => $movimento->id,
            ]);
        }
    }

}
