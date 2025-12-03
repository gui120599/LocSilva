<?php

namespace App\Observers;

use App\Models\Aluguel;
use App\Models\MovimentoCaixa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AluguelObserver
{
    /**
     * Executado ANTES de criar um aluguel
     */
    public function creating(Aluguel $aluguel): void {

    }

    /**
     * Executado APÓS criar um aluguel
     */
    public function created(Aluguel $aluguel): void
    {
            // 1️⃣ Atualizar status da carreta para "alugada"
            if ($aluguel->carreta_id && $aluguel->carreta) {
                $aluguel->carreta->update(['status' => 'alugada']);

                Log::info("✅ Carreta atualizada para 'alugada'", [
                    'aluguel_id' => $aluguel->id,
                    'carreta_id' => $aluguel->carreta_id,
                    'carreta_identificacao' => $aluguel->carreta->identificacao,
                ]);
            }
          
    }

    /**
     * Executado ANTES de atualizar um aluguel
     */
    public function updating(Aluguel $aluguel): void
    {
        // Recalcular saldo se valor_pago ou valor_total mudou
        if ($aluguel->isDirty(['valor_pago_aluguel', 'valor_total_aluguel'])) {
            $aluguel->valor_saldo_aluguel = $aluguel->valor_total_aluguel - $aluguel->valor_pago_aluguel;
        }

        // Detectar mudança de status
        if ($aluguel->isDirty('status')) {
            $statusAntigo = $aluguel->getOriginal('status');
            $statusNovo = $aluguel->status;

            Log::info("Status do aluguel mudou", [
                'aluguel_id' => $aluguel->id,
                'status_antigo' => $statusAntigo,
                'status_novo' => $statusNovo,
            ]);

            // Se mudou para finalizado ou cancelado, liberar carreta
            if (in_array($statusNovo, ['finalizado', 'cancelado'])) {
                if ($aluguel->carreta) {
                    $aluguel->carreta->update(['status' => 'disponivel']);

                    Log::info("Carreta {$aluguel->carreta->identificacao} liberada (status: {$statusNovo})", [
                        'aluguel_id' => $aluguel->id,
                        'carreta_id' => $aluguel->carreta_id,
                    ]);
                }
            }

            // Se voltou para ativo, marcar carreta como alugada novamente
            if ($statusNovo === 'ativo' && in_array($statusAntigo, ['finalizado', 'cancelado'])) {
                if ($aluguel->carreta) {
                    $aluguel->carreta->update(['status' => 'alugada']);

                    Log::info("Carreta {$aluguel->carreta->identificacao} marcada como alugada novamente", [
                        'aluguel_id' => $aluguel->id,
                        'carreta_id' => $aluguel->carreta_id,
                    ]);
                }
            }
        } else {
            Log::info("Nenhum movimento de caixa associado ao aluguel", [
                'aluguel_id' => $aluguel->id,
            ]);
        }
    }

    /**
     * Executado APÓS atualizar um aluguel
     */
    public function updated(Aluguel $aluguel): void
    {
        
    }

    /**
     * Executado ANTES de deletar um aluguel (soft delete)
     */
    public function deleting(Aluguel $aluguel): void
    {
        // Se for soft delete e o aluguel estava ativo, liberar carreta
        if (!$aluguel->isForceDeleting() && $aluguel->status === 'ativo') {
            if ($aluguel->carreta) {
                $aluguel->carreta->update(['status' => 'disponivel']);

                Log::info("Carreta liberada ao deletar aluguel", [
                    'aluguel_id' => $aluguel->id,
                    'carreta_id' => $aluguel->carreta_id,
                ]);
            }
        }
    }

    /**
     * Executado ao restaurar um aluguel (se usar soft delete)
     */
    public function restored(Aluguel $aluguel): void
    {
        // Se restaurar um aluguel ativo, marcar carreta como alugada
        if ($aluguel->status === 'ativo' && $aluguel->carreta) {
            $aluguel->carreta->update(['status' => 'alugada']);

            Log::info("Carreta marcada como alugada ao restaurar aluguel", [
                'aluguel_id' => $aluguel->id,
                'carreta_id' => $aluguel->carreta_id,
            ]);
        }
    }

    /**
     * Executado ao deletar permanentemente
     */
    public function forceDeleted(Aluguel $aluguel): void
    {
        // Limpar movimentos de caixa relacionados (opcional)
        MovimentoCaixa::where('aluguel_id', $aluguel->id)->delete();

        Log::info("Aluguel deletado permanentemente", [
            'aluguel_id' => $aluguel->id,
        ]);
    }
}
