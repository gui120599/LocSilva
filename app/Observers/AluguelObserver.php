<?php

namespace App\Observers;

use App\Models\Aluguel;
use App\Models\MovimentoCaixa;
use Illuminate\Support\Facades\Log;

class AluguelObserver
{
    /**
     * Executado ANTES de criar um aluguel
     */
    /*public function creating(Aluguel $aluguel): void
    {
        // Calcular valores automaticamente se não estiverem definidos
        if (!$aluguel->quantidade_diarias && $aluguel->data_retirada && $aluguel->data_devolucao_prevista) {
            $dias = \Carbon\Carbon::parse($aluguel->data_retirada)
                ->diffInDays(\Carbon\Carbon::parse($aluguel->data_devolucao_prevista));
            $aluguel->quantidade_diarias = max(1, $dias);
        }

        if (!$aluguel->valor_total && $aluguel->valor_diaria && $aluguel->quantidade_diarias) {
            $aluguel->valor_total = $aluguel->valor_diaria * $aluguel->quantidade_diarias;
        }

        if (!isset($aluguel->valor_saldo)) {
            $aluguel->valor_saldo = $aluguel->valor_total - ($aluguel->valor_pago ?? 0);
        }

        // Buscar caixa aberto automaticamente
        if (!$aluguel->caixa_id && auth()->check()) {
            $caixaAberto = \App\Models\Caixa::where('user_id', auth()->id())
                ->where('status', 'aberto')
                ->latest()
                ->first();

            if ($caixaAberto) {
                $aluguel->caixa_id = $caixaAberto->id;
            }
        }
    }*/

    /**
     * Executado APÓS criar um aluguel
     */
    public function created(Aluguel $aluguel): void
    {
        // 1. Atualizar status da carreta para "alugada"
        if ($aluguel->carreta) {
            $aluguel->carreta->update([
                'status' => 'alugada'
            ]);

            Log::info("Carreta {$aluguel->carreta->identificacao} marcada como alugada", [
                'aluguel_id' => $aluguel->id,
                'carreta_id' => $aluguel->carreta_id,
            ]);
        }

        // 2. Registrar movimento no caixa se houver pagamento
        if ($aluguel->valor_pago > 0 && $aluguel->caixa_id) {
            MovimentoCaixa::create([
                'caixa_id' => $aluguel->caixa_id,
                'aluguel_id' => $aluguel->id,
                'tipo' => 'entrada',
                'valor' => $aluguel->valor_pago,
                'descricao' => "Pagamento inicial - Aluguel #{$aluguel->id} - {$aluguel->cliente->nome}",
                'data_movimento' => now(),
            ]);

            // Atualizar total de entradas do caixa
            $aluguel->caixa->increment('total_entradas', $aluguel->valor_pago);

            Log::info("Movimento de caixa registrado", [
                'aluguel_id' => $aluguel->id,
                'valor' => $aluguel->valor_pago,
            ]);
        }
    }

    /**
     * Executado ANTES de atualizar um aluguel
     */
    public function updating(Aluguel $aluguel): void
    {
        // Recalcular saldo se valor_pago ou valor_total mudou
        if ($aluguel->isDirty(['valor_pago', 'valor_total'])) {
            $aluguel->valor_saldo = $aluguel->valor_total - $aluguel->valor_pago;
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
        }

        // Registrar pagamento adicional no caixa
        if ($aluguel->isDirty('valor_pago') && $aluguel->caixa_id) {
            $valorAnterior = $aluguel->getOriginal('valor_pago');
            $valorAtual = $aluguel->valor_pago;
            $diferenca = $valorAtual - $valorAnterior;

            if ($diferenca > 0) {
                MovimentoCaixa::create([
                    'caixa_id' => $aluguel->caixa_id,
                    'aluguel_id' => $aluguel->id,
                    'tipo' => 'entrada',
                    'valor' => $diferenca,
                    'descricao' => "Pagamento adicional - Aluguel #{$aluguel->id}",
                    'data_movimento' => now(),
                ]);

                $aluguel->caixa->increment('total_entradas', $diferenca);

                Log::info("Pagamento adicional registrado", [
                    'aluguel_id' => $aluguel->id,
                    'valor' => $diferenca,
                ]);
            }
        }
    }

    /**
     * Executado APÓS atualizar um aluguel
     */
    public function updated(Aluguel $aluguel): void
    {
        // Você pode adicionar lógica adicional aqui se necessário
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
