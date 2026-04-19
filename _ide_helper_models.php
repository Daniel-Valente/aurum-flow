<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Empleado> $empleados
 * @property-read int|null $empleados_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereUpdatedAt($value)
 */
	class Area extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Empleado> $empleados
 * @property-read int|null $empleados_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CentroCosto whereUpdatedAt($value)
 */
	class CentroCosto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $categoria
 * @property string|null $descripcion
 * @property string $tipo_aplicacion
 * @property int $orden
 * @property bool $requiere_factura
 * @property bool $requiere_comprobante
 * @property bool $requiere_uuid
 * @property bool $permite_sin_factura
 * @property bool $aplica_iva
 * @property bool $acumulable_dia
 * @property numeric|null $tope_referencia
 * @property \Carbon\CarbonImmutable|null $vigencia_desde
 * @property \Carbon\CarbonImmutable|null $vigencia_hasta
 * @property bool $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gasto> $gastos
 * @property-read int|null $gastos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SolicitudDetalle> $solicitudDetalles
 * @property-read int|null $solicitud_detalles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereAcumulableDia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereAplicaIva($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereOrden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto wherePermiteSinFactura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereRequiereComprobante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereRequiereFactura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereRequiereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereTipoAplicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereTopeReferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereVigenciaDesde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concepto whereVigenciaHasta($value)
 */
	class Concepto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $nombre_completo
 * @property bool $must_change_password
 * @property string|null $puesto
 * @property string|null $area_departamento
 * @property int|null $area_id
 * @property int|null $centro_costo_id
 * @property string|null $rfc
 * @property string|null $curp
 * @property string|null $numero_nomina
 * @property string|null $banco_nomina
 * @property string|null $cuenta_nomina
 * @property string|null $clabe_nomina
 * @property string|null $nss
 * @property string|null $fecha_ingreso
 * @property string|null $telefono
 * @property bool $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Area|null $area
 * @property-read \App\Models\CentroCosto|null $centroCosto
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado activos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado inactivos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereAreaDepartamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereBancoNomina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCentroCostoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereClabeNomina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCuentaNomina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCurp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereFechaIngreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereMustChangePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNombreCompleto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNumeroNomina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado wherePuesto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado withoutTrashed()
 */
	class Empleado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $concepto_id
 * @property \Carbon\CarbonImmutable $fecha_gasto
 * @property numeric $monto
 * @property string|null $rfc_proveedor
 * @property string|null $uuid_factura
 * @property string|null $archivo_xml
 * @property string|null $archivo_pdf
 * @property string $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Concepto $concepto
 * @property-read \App\Models\Empleado|null $empleado
 * @property-read \App\Models\Solicitud|null $solicitud
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereArchivoPdf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereArchivoXml($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereConceptoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereFechaGasto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereRfcProveedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereSolicitudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto whereUuidFactura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gasto withoutTrashed()
 */
	class Gasto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $gasto_id
 * @property int|null $excepcion_id
 * @property string $evento
 * @property int|null $actor_id
 * @property string $origen
 * @property array<array-key, mixed>|null $datos_antes
 * @property array<array-key, mixed>|null $datos_despues
 * @property string $created_at
 * @property-read \App\Models\User|null $actor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereDatosAntes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereDatosDespues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereExcepcionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereGastoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoAuditoria whereOrigen($value)
 */
	class GastoAuditoria extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $gasto_id
 * @property string $archivo
 * @property string|null $tipo
 * @property string|null $uuid
 * @property string $validacion_manual
 * @property int|null $validado_por
 * @property numeric|null $monto
 * @property int $subido_por
 * @property string $fecha_subida
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $sat_status
 * @property string|null $sat_checked_at
 * @property int $sat_attempts
 * @property string|null $meta_cfdi
 * @property string|null $sat_last_error
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GastoComprobante> $comprobantes
 * @property-read int|null $comprobantes_count
 * @property-read \App\Models\Gasto|null $gasto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereFechaSubida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereGastoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereMetaCfdi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereSatAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereSatCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereSatLastError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereSatStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereSubidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereValidacionManual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoComprobante whereValidadoPor($value)
 */
	class GastoComprobante extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\User|null $aprobador
 * @property-read \App\Models\Gasto|null $gasto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoExcepcion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoExcepcion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GastoExcepcion query()
 */
	class GastoExcepcion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Concepto|null $concepto
 * @property-read \Spatie\Permission\Models\Role|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto vigente()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGasto withoutTrashed()
 */
	class PoliticaGasto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $politica_id
 * @property int|null $version_id
 * @property string $evento
 * @property int|null $actor_id
 * @property string $origen
 * @property array<array-key, mixed>|null $datos_antes
 * @property array<array-key, mixed>|null $datos_despues
 * @property string $created_at
 * @property-read \Spatie\Permission\Models\Role|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereDatosAntes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereDatosDespues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereOrigen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria wherePoliticaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoAuditoria whereVersionId($value)
 */
	class PoliticaGastoAuditoria extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $politica_id
 * @property int $role_id
 * @property int $concepto_id
 * @property numeric $monto_max
 * @property string $tipo_limite
 * @property bool $permite_excepcion
 * @property \Carbon\CarbonImmutable|null $vigencia_desde
 * @property \Carbon\CarbonImmutable|null $vigencia_hasta
 * @property string|null $motivo
 * @property int|null $creado_por
 * @property int|null $aprobado_por
 * @property string $approved_at
 * @property string $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Spatie\Permission\Models\Role $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereAprobadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereConceptoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereCreadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereMontoMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion wherePermiteExcepcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion wherePoliticaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereTipoLimite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereVigenciaDesde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoliticaGastoVersion whereVigenciaHasta($value)
 */
	class PoliticaGastoVersion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $cliente
 * @property string $tipo
 * @property string|null $descripcion
 * @property string|null $region
 * @property string $prioridad
 * @property string $estado_operativo
 * @property int|null $centro_costo_id
 * @property int|null $responsable_id
 * @property numeric|null $presupuesto_total
 * @property \Carbon\CarbonImmutable|null $fecha_inicio
 * @property \Carbon\CarbonImmutable|null $fecha_fin
 * @property string|null $pais
 * @property string|null $estado
 * @property string|null $ciudad
 * @property bool $estatus
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\CentroCosto|null $centroCosto
 * @property-read \App\Models\Empleado|null $responsable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereCentroCostoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereCiudad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereEstadoOperativo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereFechaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto wherePais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto wherePresupuestoTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto wherePrioridad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereResponsableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proyecto whereUpdatedAt($value)
 */
	class Proyecto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SolicitudDetalle> $detalles
 * @property-read int|null $detalles_count
 * @property-read \App\Models\Empleado|null $empleado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gasto> $gastos
 * @property-read int|null $gastos_count
 * @property-read \App\Models\Proyecto|null $proyecto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud propias($user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Solicitud withoutTrashed()
 */
	class Solicitud extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudAuditoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudAuditoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudAuditoria query()
 */
	class SolicitudAuditoria extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $concepto_id
 * @property numeric $monto_estimado
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Concepto $concepto
 * @property-read \App\Models\Solicitud|null $solicitud
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereConceptoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereMontoEstimado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereSolicitudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SolicitudDetalle whereUpdatedAt($value)
 */
	class SolicitudDetalle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property bool $must_change_password
 * @property bool $active
 * @property-read \App\Models\Empleado|null $empleado
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMustChangePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 */
	class User extends \Eloquent {}
}

