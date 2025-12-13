<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyDemand extends Model
{
    protected $table = 'daily_demands';

    protected $fillable = [
        'data_diaria',
        'qtd_funcionarios',
    ];

    protected $casts = [
        'data_diaria' => 'date',
    ];
}
