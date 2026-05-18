<?php

namespace App\Console\Commands;

use App\Models\Presupuesto;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:renovar-presupuestos')]
#[Description('Command description')]
class RenovarPresupuestos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presupuestos:renovar
                            {--tipo= : Tipo de presupuesto a renovar (diario, semanal, quincenal, mensual)}
                            {--dry-run : Simular sin crear presupuestos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renovar presupuestos que han vencido y están marcados como renovables';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $tipo = $this->option('tipo');

        $this->info('🔄 Iniciando renovación de presupuestos...');

        $query = Presupuesto::where('renovable', true)
            ->where('estatus', 'activo')
            ->where('fecha_fin', '<', now());

        if ($tipo) {
            $query->where('frecuencia_renovacion', $tipo);
        }

        $presupuestos = $query->get();

        if ($presupuestos->isEmpty()) {
            $this->info('✅ No hay presupuestos para renovar.');
            return 0;
        }

        $this->info("📋 Encontrados {$presupuestos->count()} presupuesto(s) para renovar.");

        $renovados = 0;
        $errores = 0;

        foreach ($presupuestos as $presupuesto) {
            try {
                if ($isDryRun) {
                    $this->line("  [DRY-RUN] Renovaría: {$presupuesto->codigo} ({$presupuesto->nombre})");
                    $renovados++;
                    continue;
                }

                $nuevo = $this->renovarPresupuesto($presupuesto);

                $this->info("  ✓ Renovado: {$presupuesto->codigo} → {$nuevo->codigo}");
                $renovados++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error renovando {$presupuesto->codigo}: {$e->getMessage()}");
                $errores++;
            }
        }

        $this->newLine();
        $this->info("🎉 Proceso completado:");
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Renovados', $renovados],
                ['Errores', $errores],
            ]
        );

        return $errores > 0 ? 1 : 0;
    }

    private function renovarPresupuesto(Presupuesto $presupuesto): Presupuesto
    {
        [$fechaInicio, $fechaFin] = $this->calcularNuevasFechas($presupuesto);

        $nuevo = Presupuesto::create([
            'codigo' => $this->generarNuevoCodigo($presupuesto),
            'nombre' => $presupuesto->nombre,
            'descripcion' => $presupuesto->descripcion,
            'tipo' => $presupuesto->tipo,
            'empresa_id' => $presupuesto->empresa_id,
            'area_id' => $presupuesto->area_id,
            'empleado_id' => $presupuesto->empleado_id,
            'proyecto_id' => $presupuesto->proyecto_id,
            'monto_total' => $presupuesto->monto_total,
            'monto_gastado' => 0,
            'monto_comprometido' => 0,
            'periodo' => $presupuesto->periodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'renovable' => $presupuesto->renovable,
            'frecuencia_renovacion' => $presupuesto->frecuencia_renovacion,
            'alerta_porcentaje' => $presupuesto->alerta_porcentaje,
            'critico_porcentaje' => $presupuesto->critico_porcentaje,
            'activo' => true,
            'estatus' => 'activo',
            'creado_por' => $presupuesto->creado_por,
            'aprobado_por' => $presupuesto->aprobado_por,
            'aprobado_en' => now(),
        ]);

        $presupuesto->update(['estatus' => 'vencido']);

        return $nuevo;
    }

    private function calcularNuevasFechas(Presupuesto $presupuesto): array
    {
        $inicio = Carbon::now();

        $fin = match ($presupuesto->frecuencia_renovacion) {
            'diario' => $inicio->copy()->endOfDay(),
            'semanal' => $inicio->copy()->endOfWeek(),
            'quincenal' => $inicio->copy()->addDays(14)->endOfDay(),
            'mensual' => $inicio->copy()->endOfMonth(),
            'trimestral' => $inicio->copy()->addMonths(3)->endOfDay(),
            'semestral' => $inicio->copy()->addMonths(6)->endOfDay(),
            'anual' => $inicio->copy()->endOfYear(),
            default => $inicio->copy()->endOfMonth(),
        };

        return [$inicio, $fin];
    }

    private function generarNuevoCodigo(Presupuesto $presupuesto): string
    {
        $prefijo = explode('-', $presupuesto->codigo)[0];
        $año = now()->year;

        $ultimo = Presupuesto::where('codigo', 'like', "{$prefijo}-{$año}-%")
            ->orderByDesc('id')
            ->value('codigo');

        if ($ultimo) {
            $numero = (int) substr($ultimo, -4) + 1;
        } else {
            $numero = 1;
        }

        return sprintf('%s-%d-%04d', $prefijo, $año, $numero);
    }
}
