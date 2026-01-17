<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'filial_id',
        'data_diaria',
        'hora_inicio',
        'hora_fim',
        'vagas_totais',
        'created_by',
        'updated_by',
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

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
