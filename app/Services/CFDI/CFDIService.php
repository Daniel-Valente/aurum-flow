<?php

namespace App\Services\CFDI;

use App\Models\Gasto;

class CFDIService
{
    public function procesar($file, Gasto $gasto): array
    {
        $path = $file->getRealPath();

        if (!$path || !file_exists($path)) {
            throw new \Exception('Archivo CFDI no encontrado');
        }

        $xmlContent = file_get_contents($path);

        if ($xmlContent === false || empty(trim($xmlContent))) {
            throw new \Exception('No se pudo leer el archivo CFDI');
        }

        $data = app(CFDIParser::class)->parse($xmlContent);

        app(CFDIValidator::class)->validar($data, $gasto);

        return [
            ...$data,
            'sat_status' => 'pendiente',
        ];
    }
}
