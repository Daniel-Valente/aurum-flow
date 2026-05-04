<?php

namespace App\Models;

use App\Models\Traits\EvaluaComprobacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class PoliticaGasto extends Model
{
    use SoftDeletes, EvaluaComprobacion;

    protected $table = 'politicas_gastos';

    protected $fillable = [
        'role_id',
        'concepto_id',

        // Monto máximo autorizado en este período/tipo
        'monto_max',

        // Diario | Viaje | Evento
        'tipo_limite',

        // --- Tramos documentales (todos nullable) ---
        // null en monto_libre  = siempre se requiere algo desde $0.01
        // null en monto_comprobante = no hay tramo intermedio de ticket
        // null en monto_factura     = nunca se exige CFDI (solo comprobante)
        'monto_libre',
        'monto_comprobante',
        'monto_factura',

        // Al recibir un CFDI con UUID, consultar la API del SAT para validarlo
        'valida_sat',

        // El concepto puede acumularse varias veces en el mismo día (por rol)
        'acumulable_dia',

        // Se puede superar monto_max con justificación aprobada
        'permite_excepcion',

        'vigencia_desde',
        'vigencia_hasta',

        'estatus',
    ];

    protected $casts = [
        'monto_max'         => 'decimal:2',
        'monto_libre'       => 'decimal:2',
        'monto_comprobante' => 'decimal:2',
        'monto_factura'     => 'decimal:2',
        'valida_sat'        => 'boolean',
        'acumulable_dia'    => 'boolean',
        'permite_excepcion' => 'boolean',
        'estatus'           => 'boolean',
        'vigencia_desde'    => 'date',
        'vigencia_hasta'    => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function versiones()
    {
        return $this->hasMany(PoliticaGastoVersion::class, 'politica_id');
    }

    public function auditorias()
    {
        return $this->hasMany(PoliticaGastoAuditoria::class, 'politica_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeVigente($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', now());
            });
    }

    /**
     * Valida que el documento adjunto cumple el nivel requerido para el monto.
     *
     * @param  float        $monto
     * @param  string|null  $tipoDocumento  'ticket' | 'cfdi' | null
     * @param  string|null  $uuid           UUID del CFDI si tipoDocumento === 'cfdi'
     * @return array{valido: bool, error?: string, nivel?: string}
     */
    public function validarDocumento(float $monto, ?string $tipoDocumento, ?string $uuid = null): array
    {
        $nivel = $this->evaluarComprobacion($monto);

        if ($nivel === 'ninguno') {
            return ['valido' => true, 'nivel' => $nivel];
        }

        if ($nivel === 'comprobante' && $tipoDocumento === null) {
            return [
                'valido' => false,
                'nivel'  => $nivel,
                'error'  => 'Se requiere comprobante (ticket o CFDI) para este monto.',
            ];
        }

        if ($nivel === 'cfdi') {
            if ($tipoDocumento !== 'cfdi') {
                return [
                    'valido' => false,
                    'nivel'  => $nivel,
                    'error'  => "Se requiere CFDI para montos ≥ \${$this->monto_factura}.",
                ];
            }

            if ($this->valida_sat && empty($uuid)) {
                return [
                    'valido' => false,
                    'nivel'  => $nivel,
                    'error'  => 'El CFDI debe incluir un UUID válido para validación ante el SAT.',
                ];
            }
        }

        return ['valido' => true, 'nivel' => $nivel];
    }

    public function getEstadoVigenciaAttribute()
    {
        $now = now();

        if (is_null($this->vigencia_desde) && is_null($this->vigencia_hasta)) {
            return 'Sin vigencia';
        }

        if ($this->vigencia_desde && $this->vigencia_desde > $now) {
            return 'Futura';
        }

        if ($this->vigencia_hasta && $this->vigencia_hasta < $now) {
            return 'Expirada';
        }

        return 'Vigente';
    }
}

