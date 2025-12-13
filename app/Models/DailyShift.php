<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'data_diaria',
        'hora_inicio',
        'hora_fim',
        'vagas_totais',
    ];

    protected $casts = [
        'data_diaria' => 'date',
    ];

    public function requests()
    {
        return $this->hasMany(DailyRequest::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
