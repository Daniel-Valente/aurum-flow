<?php

namespace App\Jobs;

use App\Models\GastoComprobante;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use PhpCfdi\SatEstadoCfdi\Clients\Soap\SoapConsumerClient;
use PhpCfdi\SatEstadoCfdi\Consumer;

class ValidarCFDIJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900, 1800, 3600]; // 1m, 5m, 15m, 30m, 1h

    public function __construct(
        public int $comprobanteId,
        public array $cfdiData
    ) {
        $this->onQueue('sat_high');
    }

    public function handle(): void
    {
        $comprobante = GastoComprobante::find($this->comprobanteId);

        if (!$comprobante || in_array($comprobante->sat_status, ['vigente', 'cancelado'])) {
            return;
        }

        try {
            $comprobante->update(['sat_status' => 'validando']);

            $client = new SoapConsumerClient();
            $consumer = new Consumer($client);

            $rfcEmisor   = strtoupper((string) ($this->cfdiData['rfc_emisor'] ?? ''));
            $rfcReceptor = strtoupper((string) ($this->cfdiData['rfc_receptor'] ?? ''));
            $totalCfdi   = (float) ( $this->cfdiData['total'] ?? 0.0);
            $uuid = (string) ($this->cfdiData['uuid'] ?? '');

            $simpleExpression = "?re=$rfcEmisor&rr=$rfcReceptor&tt=$totalCfdi&id=$uuid";

            $response = $consumer->execute($simpleExpression);

            // ✅ Forma correcta
            if ($response->document->isActive()) {
                $estado = 'vigente';
            } elseif ($response->document->isCancelled()) {
                $estado = 'cancelado';
            } else {
                $estado = 'no_encontrado';
            }

            $comprobante->update([
                'sat_status'     => $estado,
                'sat_checked_at' => now(),
                'sat_attempts'   => ($comprobante->sat_attempts ?? 0) + 1,
                'sat_last_error' => null,
            ]);

        } catch (\Throwable $e) {
            $attempts = ($comprobante->sat_attempts ?? 0) + 1;

            $comprobante->update([
                'sat_status'     => 'error',
                'sat_attempts'   => $attempts,
                'sat_last_error' => $e->getMessage(),
            ]);

            if ($attempts < $this->tries) {
                $this->release($this->backoff[$attempts - 1] ?? 3600);
            } else {
                // ✅ Después de 5 intentos, marcar como 'no_encontrado' y notificar
                $comprobante->update(['sat_status' => 'no_encontrado']);
                \Log::error('CFDI validation failed after 5 attempts', [
                    'comprobante_id' => $comprobante->id,
                    'uuid' => $this->cfdiData['uuid'],
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e; // Laravel marcará el job como failed
        }
    }
}
