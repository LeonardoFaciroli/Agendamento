<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroPresenca extends Model
{
    protected $table = 'registros_presenca';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'filial_id',
        'data_presenca',
        'hora_entrada',
        'hora_saida',
        'status_presenca',
        'status_pagamento',
        'pagamento_id',
        'valor_diaria',
        'data_pagamento',
        'horas_trabalhadas',
    ];

    protected $casts = [
        'data_presenca'   => 'date',
        'hora_entrada'    => 'string',
        'hora_saida'      => 'string',
        'data_pagamento'  => 'datetime',
        'horas_trabalhadas' => 'decimal:2',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function pagamento(): BelongsTo
    {
        return $this->belongsTo(Pagamento::class, 'pagamento_id');
    }
}
