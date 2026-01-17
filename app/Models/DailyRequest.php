<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRequest extends Model
{
    use HasFactory;

    protected $table = 'daily_requests';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'filial_id',
        'data_diaria',
        'daily_shift_id',
        'status',
        'observacoes',
        'aprovado_por',
    ];

    protected $casts = [
        'data_diaria' => 'date',
    ];

public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

public function dailyShift()
    {
        return $this->belongsTo(DailyShift::class, 'daily_shift_id');
    }
}
