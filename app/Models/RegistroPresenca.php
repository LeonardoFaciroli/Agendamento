<?php

// app/Models/RegistroPresenca.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroPresenca extends Model
{
    protected $table = 'registros_presenca';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'data_presenca',
        'hora_entrada',
        'hora_saida',
        'status_presenca',
        'status_pagamento',
        'valor_diaria',
        'data_pagamento',
        'horas_trabalhadas',
    ];

    protected $casts = [
        'data_presenca'   => 'date',
        // Guardamos apenas hora/minuto/segundo; mantemos como string para evitar
        // concatenações com datas erradas ao calcular a jornada.
        'hora_entrada'    => 'string',
        'hora_saida'      => 'string',
        'data_pagamento'  => 'datetime',
        'horas_trabalhadas' => 'decimal:2',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
