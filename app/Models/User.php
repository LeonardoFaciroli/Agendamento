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
        'filial_id',
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

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function dailyRequests()
    {
        return $this->hasMany(DailyRequest::class, 'user_id');
    }

    public function registrosPresenca()
    {
        return $this->hasMany(RegistroPresenca::class, 'user_id');
    }

    public function diarista()
    {
        return $this->hasOne(Diarista::class, 'user_id');
    }

    public function gestor()
    {
        return $this->hasOne(Gestor::class, 'user_id');
    }

    public function supervisor()
    {
        return $this->hasOne(Supervisor::class, 'user_id');
    }

    public function rh()
    {
        return $this->hasOne(Rh::class, 'user_id');
    }

    public function adminProfile()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    public function isGestor(): bool
    {
        return in_array($this->role, ['gestor', 'empresa', 'gerente'], true);
    }

    public function isSupervisor(): bool
    {
        return in_array($this->role, ['supervisor', 'porteiro'], true);
    }

    public function isRh(): bool
    {
        return $this->role === 'rh';
    }

    public function isDiarista(): bool
    {
        return in_array($this->role, ['diarista', 'funcionario'], true);
    }

    public function isEmpresa(): bool
    {
        return $this->isGestor();
    }

    public function isFuncionario(): bool
    {
        return $this->isDiarista();
    }

    public function isGerente(): bool
    {
        return $this->isGestor();
    }

    public function isPorteiro(): bool
    {
        return $this->isSupervisor();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

public function podeRegistrarPresenca(): bool
    {
        return $this->isSupervisor();
    }

    public function podeGerenciarEscala(): bool
    {
        return $this->isGestor() || $this->isRh();
    }
}
