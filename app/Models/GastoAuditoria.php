<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoAuditoria extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'gastos_auditoria';

    const UPDATED_AT = null;

    protected $fillable = [
        'gasto_id',
        'excepcion_id',
        'evento',
        'actor_id',
        'origen',
        'datos_antes',
        'datos_despues'
    ];

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array'
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
