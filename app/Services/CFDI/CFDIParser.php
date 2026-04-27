<?php

namespace App\Services\CFDI;

class CFDIParser
{
    public function parse(string $xmlContent): array
    {
        // ✅ Protección contra XXE (XML External Entity injection)
        // Un XML malicioso podría leer /etc/passwd, .env, etc. del servidor.
        // En PHP >= 8.0 las entidades externas ya están deshabilitadas por defecto,
        // pero se mantiene la llamada por compatibilidad con PHP 7.x.
        $previousLoader = libxml_set_external_entity_loader(null);
        $useInternalErrors = libxml_use_internal_errors(true);

        $xml = simplexml_load_string(
            $xmlContent,
            'SimpleXMLElement',
            LIBXML_NONET   // no permite acceso a red durante el parse
        );

        libxml_use_internal_errors($useInternalErrors);
        libxml_set_external_entity_loader($previousLoader);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $msg = !empty($errors) ? $errors[0]->message : 'XML inválido';
            throw new \Exception('CFDI: ' . trim($msg));
        }

        $namespaces = $xml->getNamespaces(true);

        if (!isset($namespaces['cfdi'])) {
            throw new \Exception('El XML no contiene namespace cfdi válido');
        }

        $cfdi = $xml->children($namespaces['cfdi']);

        // Timbre Fiscal Digital — requerido para CFDI timbrado
        $tfdNs = $namespaces['tfd'] ?? null;

        if (!$tfdNs) {
            throw new \Exception('No es un CFDI timbrado válido (falta namespace tfd)');
        }

        $complemento = $cfdi->Complemento ?? null;

        if (!$complemento) {
            throw new \Exception('CFDI sin nodo Complemento');
        }

        $timbre = $complemento->children($tfdNs)->TimbreFiscalDigital ?? null;

        if (!$timbre) {
            throw new \Exception('No se encontró TimbreFiscalDigital en el Complemento');
        }

        $uuid = (string) ($timbre['UUID'] ?? '');

        if (empty($uuid)) {
            throw new \Exception('CFDI sin UUID en TimbreFiscalDigital');
        }

        return [
            'uuid'          => strtoupper($uuid),
            'rfc_emisor'    => strtoupper((string) ($cfdi->Emisor['Rfc']    ?? '')),
            'rfc_receptor'  => strtoupper((string) ($cfdi->Receptor['Rfc']  ?? '')),
            'total'         => (float)  ($cfdi['Total']  ?? 0),
            'fecha'         => (string) ($cfdi['Fecha']  ?? ''),
            // ✅ Sello del emisor — usado por SATService para el parámetro 'fe'
            'sello'         => (string) ($cfdi['Sello']  ?? ''),
        ];
    }
}
