<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    use HasFactory;

    protected $table = 'filiais';

    protected $fillable = [
        'empresa_id',
        'nome',
        'cidade',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'filial_id');
    }

    public function dailyShifts()
    {
        return $this->hasMany(DailyShift::class, 'filial_id');
    }

    public function dailyRequests()
    {
        return $this->hasMany(DailyRequest::class, 'filial_id');
    }

    public function registrosPresenca()
    {
        return $this->hasMany(RegistroPresenca::class, 'filial_id');
    }
}
