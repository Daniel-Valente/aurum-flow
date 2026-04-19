<?php

namespace App\Services\CFDI;

use App\Models\Gasto;

class CFDIService
{
    public function procesar($file, Gasto $gasto): array
    {
        $xmlContent = file_get_contents($file->getRealPath());

        $data = app(CFDIParser::class)->parse($xmlContent);

        app(CFDIValidator::class)->validar($data, $gasto);

        return [
            ...$data,
            'sat_status' => 'pendiente'
        ];
    }
}
