<?php
// Indica que este arquivo contém código PHP
namespace App\Models; // Define o namespace da classe (pasta lógica dentro do projeto)

// Importa a classe base Model do Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory; // Importa trait para usar factories
use Illuminate\Database\Eloquent\Model;                // Importa a classe Model

// Declara a classe Empresa que representa a tabela 'empresas'
class Empresa extends Model
{
    // Usa a trait HasFactory para facilitar criação de instâncias em testes
    use HasFactory;

    // Define quais colunas podem ser preenchidas em massa (mass assignment)
    protected $fillable = [
        'nome', // Nome da empresa
        'billing_status', // active, past_due, canceled
        'paid_until',
        'mercadopago_preapproval_id',
        'mercadopago_payer_id',
        'billing_plan',
    ];

    protected $casts = [
        'paid_until' => 'date',
    ];

    // Define o relacionamento: uma empresa tem muitos usuários
    public function usuarios()
    {
        // Retorna a relação hasMany com o model User
        return $this->hasMany(User::class, 'empresa_id');
    }

    // Define o relacionamento: uma empresa tem muitas requisições de diaria
    public function dailyRequests()
    {
        // Retorna a relação hasMany com o model DailyRequest
        return $this->hasMany(DailyRequest::class, 'empresa_id');
    }
}
