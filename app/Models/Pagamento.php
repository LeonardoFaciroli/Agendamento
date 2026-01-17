<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pagamento extends Model
{
    protected $fillable = [
        'user_id',
        'empresa_id',
        'filial_id',
        'dias_pagos',
        'comprovante_path',
        'data_pagamento',
        'pago_por',
    ];

    protected $casts = [
        'data_pagamento' => 'datetime',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrosPresenca(): HasMany
    {
        return $this->hasMany(RegistroPresenca::class, 'pagamento_id');
    }

    public function pagoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pago_por');
    }
}
