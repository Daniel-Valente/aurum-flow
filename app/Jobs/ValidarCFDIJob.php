<?php

namespace App\Jobs;

use App\Models\GastoComprobante;
use App\Services\CFDI\SATService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidarCFDIJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $comprobanteId;
    protected $cfdiData;

    public function __construct($comprobanteId, array $cfdiData)
    {
        $this->comprobanteId = $comprobanteId;
        $this->cfdiData = $cfdiData;

        $this->onQueue('sat_high');
    }

    public function handle()
    {
        $comprobante = GastoComprobante::find($this->comprobanteId);

        if (!$comprobante) return;

        if (in_array($comprobante->sat_status, ['vigente', 'cancelado'])) {
            return;
        }

        try {

            $comprobante->update(['sat_status' => 'validando']);

            $sat = app(SATService::class)->validar($this->cfdiData);

            $comprobante->update([
                'sat_status' => $sat['estatus'],
                'sat_checked_at' => now(),
                'sat_attempts' => $comprobante->sat_attempts + 1,
                'sat_last_error' => null,
            ]);

        } catch (\Throwable $e) {

            $attempts = $comprobante->sat_attempts + 1;

            $comprobante->update([
                'sat_status' => 'error',
                'sat_attempts' => $attempts,
                'sat_last_error' => $e->getMessage(),
            ]);

            $delay = match ($attempts) {
                1 => 60,
                2 => 300,
                3 => 900,
                4 => 1800,
                default => 3600
            };

            if ($attempts < 5) {
                dispatch(new ReintentarCFDIJob($comprobante->id, $this->cfdiData))
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('sat_low');
            }
        }
    }
}
