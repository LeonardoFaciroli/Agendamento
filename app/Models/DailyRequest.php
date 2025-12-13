<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRequest extends Model
{
    use HasFactory;

    protected $table = 'daily_requests';

    protected $fillable = [
        'user_id',         // ID do funcionário que fez a requisição
        'empresa_id',      // ID da empresa ligada à requisição
        'data_diaria',     // Data da diária
        'daily_shift_id',  // ID do turno/horário
        'status',          // Status da requisição
        'observacoes',     // Observações da requisição
        'aprovado_por',    // ID do usuário que aprovou/recusou
    ];

    protected $casts = [
        'data_diaria' => 'date',
    ];

    /**
     * Funcionário que fez a requisição.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Empresa dona da requisição.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Usuário (empresa/gestor) que aprovou.
     */
    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    /**
     * Turno/horário escolhido.
     */
    public function dailyShift()
    {
        return $this->belongsTo(DailyShift::class, 'daily_shift_id');
    }
}
