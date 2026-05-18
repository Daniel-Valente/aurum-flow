<?php

namespace App\Console\Commands;

use App\Jobs\NotificarAlertaPresupuestoJob;
use App\Models\Presupuesto;
use App\Models\PresupuestoAlerta;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verificar-alertas-presupuestos')]
#[Description('Command description')]
class VerificarAlertasPresupuestos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presupuestos:verificar-alertas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar presupuestos y generar alertas según consumo y días restantes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando alertas de presupuestos...');

        $presupuestos = Presupuesto::activos()->get();

        if ($presupuestos->isEmpty()) {
            $this->info('✅ No hay presupuestos activos.');
            return 0;
        }

        $alertasGeneradas = 0;

        foreach ($presupuestos as $presupuesto) {
            $alertas = $this->verificarPresupuesto($presupuesto);
            $alertasGeneradas += $alertas;
        }

        $this->info("✅ Proceso completado. {$alertasGeneradas} alerta(s) generada(s).");
        return 0;
    }

    private function verificarPresupuesto(Presupuesto $presupuesto): int
    {
        $alertas = 0;

        if ($presupuesto->porcentaje_consumido >= $presupuesto->alerta_porcentaje &&
            $presupuesto->porcentaje_consumido < $presupuesto->critico_porcentaje) {

            $existe = PresupuestoAlerta::where('presupuesto_id', $presupuesto->id)
                ->where('tipo', 'alerta')
                ->where('resuelto', false)
                ->exists();

            if (!$existe) {
                $this->generarAlerta($presupuesto, 'alerta', 'warning');
                $alertas++;
            }
        }

        if ($presupuesto->porcentaje_consumido >= $presupuesto->critico_porcentaje &&
            $presupuesto->porcentaje_consumido < 100) {

            $existe = PresupuestoAlerta::where('presupuesto_id', $presupuesto->id)
                ->where('tipo', 'critico')
                ->where('resuelto', false)
                ->exists();

            if (!$existe) {
                $this->generarAlerta($presupuesto, 'critico', 'danger');
                $alertas++;
            }
        }

        if ($presupuesto->porcentaje_consumido >= 100) {
            $existe = PresupuestoAlerta::where('presupuesto_id', $presupuesto->id)
                ->where('tipo', 'agotado')
                ->where('resuelto', false)
                ->exists();

            if (!$existe) {
                $this->generarAlerta($presupuesto, 'agotado', 'critical');
                $alertas++;
            }
        }

        if ($presupuesto->dias_restantes !== null &&
            $presupuesto->dias_restantes <= 7 &&
            $presupuesto->dias_restantes > 0) {

            $existe = PresupuestoAlerta::where('presupuesto_id', $presupuesto->id)
                ->where('tipo', 'proximo_vencer')
                ->where('resuelto', false)
                ->exists();

            if (!$existe) {
                $this->generarAlerta($presupuesto, 'proximo_vencer', 'warning');
                $alertas++;
            }
        }

        return $alertas;
    }

    private function generarAlerta(Presupuesto $presupuesto, string $tipo, string $severidad): void
    {
        $mensajes = [
            'alerta' => "El presupuesto {$presupuesto->codigo} ha alcanzado el {$presupuesto->porcentaje_consumido}% de consumo.",
            'critico' => "⚠️ CRÍTICO: El presupuesto {$presupuesto->codigo} está al {$presupuesto->porcentaje_consumido}%.",
            'agotado' => "🔴 AGOTADO: El presupuesto {$presupuesto->codigo} se ha consumido completamente.",
            'proximo_vencer' => "El presupuesto {$presupuesto->codigo} vence en {$presupuesto->dias_restantes} días.",
        ];

        $alerta = PresupuestoAlerta::create([
            'presupuesto_id' => $presupuesto->id,
            'tipo' => $tipo,
            'severidad' => $severidad,
            'titulo' => match($tipo) {
                'alerta' => 'Presupuesto en alerta',
                'critico' => 'Presupuesto crítico',
                'agotado' => 'Presupuesto agotado',
                'proximo_vencer' => 'Presupuesto próximo a vencer',
            },
            'mensaje' => $mensajes[$tipo],
            'porcentaje_consumido' => $presupuesto->porcentaje_consumido,
            'monto_disponible' => $presupuesto->monto_disponible,
            'dias_restantes' => $presupuesto->dias_restantes,
        ]);

        NotificarAlertaPresupuestoJob::dispatch($alerta);

        $this->line("  ⚠️  {$tipo}: {$presupuesto->codigo}");
    }
}
