<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PoliticaGastoAuditoria extends Model
{
    // Tabla append-only: solo created_at, sin updated_at
    public $timestamps = false;

    protected $table = 'politicas_gastos_auditoria';

    protected $fillable = [
        'politica_id',
        'version_id',

        // created | updated | deleted | status_changed | approved
        'evento',

        'actor_id',

        // sistema | api | manual
        'origen',

        'datos_antes',
        'datos_despues',
    ];

    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
        'created_at'    => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function politica()
    {
        return $this->belongsTo(PoliticaGasto::class, 'politica_id');
    }

    public function version()
    {
        return $this->belongsTo(PoliticaGastoVersion::class, 'version_id');
    }

    /**
     * Usuario que realizó la acción.
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePorEvento($query, string $evento)
    {
        return $query->where('evento', $evento);
    }

    public function scopePorActor($query, int $userId)
    {
        return $query->where('actor_id', $userId);
    }
}
