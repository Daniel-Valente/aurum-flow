<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAuditoria extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id',
        'evento',
        'actor_id',
        'datos'
    ];

    protected $casts = [
        'datos' => 'array'
    ];
}
