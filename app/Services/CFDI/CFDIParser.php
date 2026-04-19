<?php

namespace App\Services\CFDI;

class CFDIParser
{
    public function parse(string $xmlContent): array
    {
        $xml = simplexml_load_string($xmlContent);

        if (!$xml) {
            throw new \Exception('XML inválido');
        }

        $namespaces = $xml->getNamespaces(true);

        $cfdi = $xml->children($namespaces['cfdi']);
        $tfd = $xml->children($namespaces['tfd'] ?? null);

        $complemento = $cfdi->Complemento ?? null;

        $timbre = $complemento
            ? $complemento->children($namespaces['tfd'])->TimbreFiscalDigital ?? null
            : null;

        if (!$timbre) {
            throw new \Exception('No es un CFDI timbrado válido');
        }

        return [
            'uuid' => (string) $timbre['UUID'],
            'rfc_emisor' => (string) $cfdi->Emisor['Rfc'],
            'rfc_receptor' => (string) $cfdi->Receptor['Rfc'],
            'total' => (float) $cfdi['Total'],
            'fecha' => (string) $cfdi['Fecha'],
        ];


    }
}
