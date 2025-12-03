<?php

namespace App\Observers;

use App\Models\Caixa;
use App\Models\MovimentoCaixa;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class MovimentoCaixaObserver
{
    /**
     * Antes de criar um movimento de caixa
     */
    public function creating(MovimentoCaixa $movimento): bool
    {
        return true;
    }

    /**
     * Após criar
     */
    public function created(MovimentoCaixa $movimento): void
    {
        if ($movimento->valor_total_movimento <= 0) {
            Log::warning("🚫 Movimento com valor inválido criado", [
                'movimento_id' => $movimento->id,
                'valor_total_movimento' => $movimento->valor_total_movimento,
            ]);
            $movimento->delete();

            Notification::make()
                ->title('Movimento inválido removido')
                ->body('Um movimento de caixa com valor inválido foi criado e removido automaticamente.')
                ->warning()
                ->send();
        } else {
            $caixaId = Caixa::where('status', 'aberto')
                ->where('user_id', $movimento->user_id)
                ->first();
                
            // Verifica se o movimento está vinculado a um aluguel e altera a descrição e vinculação ao caixa
            if ($movimento->aluguel_id) {
                $movimento->update([
                    'descricao' => "Pagamento R$" .
                        number_format($movimento->valor_total_movimento, 2, ',', '.') .
                        " - Aluguel #{$movimento->aluguel_id}",
                    'caixa_id' => $caixaId ? $caixaId->id : null,
                ]);
            }




            Log::info("💰 Movimento criado com sucesso", [
                'movimento_id' => $movimento->id,
                'valor_total_movimento' => $movimento->valor_total_movimento,
                'tipo' => $movimento->tipo,
                'caixa_id' => $movimento->caixa_id,
            ]);
        }
    }



    /**
     * Antes de atualizar
     */
    public function updating(MovimentoCaixa $movimento): bool
    {
        $valor = floatval($movimento->valor_total_movimento ?? 0);

        if ($valor <= 0) {
            Log::warning("🚫 Update bloqueado — valor inválido", [
                'movimento_id' => $movimento->id,
                'valor_total_movimento' => $valor,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Após atualizar
     */
    public function updated(MovimentoCaixa $movimento): void
    {
        Log::info("✏️ Movimento atualizado", [
            'movimento_id' => $movimento->id,
            'valor_total_movimento' => $movimento->valor_total_movimento,
        ]);
    }

    /**
     * Antes de excluir
     */
    public function deleting(MovimentoCaixa $movimento): void
    {
        Log::info("🗑️ Excluindo movimento", [
            'movimento_id' => $movimento->id,
            'valor_total_movimento' => $movimento->valor_total_movimento,
            'aluguel_id' => $movimento->aluguel_id,
        ]);
    }

    /**
     * Após excluir
     */
    public function deleted(MovimentoCaixa $movimento): void
    {
        Log::info("✔️ Movimento excluído", [
            'movimento_id' => $movimento->id,
            'aluguel_id' => $movimento->aluguel_id,
        ]);
    }
}
