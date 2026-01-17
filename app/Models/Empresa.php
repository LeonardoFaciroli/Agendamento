<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'billing_status',
        'paid_until',
        'mercadopago_preapproval_id',
        'mercadopago_payer_id',
        'billing_plan',
    ];

    protected $casts = [
        'paid_until' => 'date',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function filiais()
    {
        return $this->hasMany(Filial::class, 'empresa_id');
    }

    public function dailyRequests()
    {
        return $this->hasMany(DailyRequest::class, 'empresa_id');
    }

}
