<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'empresa_id',
        'qr_token',
        'invitation_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function dailyRequests()
    {
        return $this->hasMany(DailyRequest::class, 'user_id');
    }

    public function registrosPresenca()
    {
        return $this->hasMany(RegistroPresenca::class, 'user_id');
    }

    public function isEmpresa(): bool
    {
        return $this->role === 'empresa';
    }

    public function isFuncionario(): bool
    {
        return $this->role === 'funcionario';
    }

    public function isGerente(): bool
    {
        return $this->role === 'gerente';
    }

    public function isPorteiro(): bool
    {
        return $this->role === 'porteiro';
    }

    /**
     * Pode registrar presença via QR: empresa, gerente e porteiro.
     */
    public function podeRegistrarPresenca(): bool
    {
        return $this->isEmpresa() || $this->isGerente() || $this->isPorteiro();
    }
}
