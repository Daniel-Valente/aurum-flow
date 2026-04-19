<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class ReintentarCFDIJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public $comprobanteId,
        public array $cfdiData
    ) {
        $this->onQueue('sat_low');
    }

    public function handle()
    {
        dispatch(new ValidarCFDIJob(
            $this->comprobanteId,
            $this->cfdiData
        ))->onQueue('sat_high');
    }
}
