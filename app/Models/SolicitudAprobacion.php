<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class SolicitudAprobacion extends Model
{
    public $timestamps = false;

    protected $table = 'solicitud_aprobaciones';

    protected $fillable = [
        'solicitud_id',
        'role_id',
        'user_id',
        'accion',
        'comentario',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
