<?php

namespace App\Observers;

use App\Models\Aluguel;

class AluguelObserver
{
    public function deleted(Aluguel $aluguel): void
    {
        // Executado no soft delete
        if (!$aluguel->isForceDeleting()) {
            // Libera a carreta
            $aluguel->carreta->update(['status' => 'disponivel']);
        }
    }

    public function restored(Aluguel $aluguel): void
    {
        // Executado ao restaurar
        $aluguel->carreta->update(['status' => 'alugada']);
    }

    public function forceDeleted(Aluguel $aluguel): void
    {
        // Executado na exclusão permanente
        // Limpar registros relacionados se necessário
    }
}
