<?php
// Abre a tag PHP para informar que o arquivo contém código PHP
use Illuminate\Database\Migrations\Migration; // Importa a classe base de migração do Laravel
use Illuminate\Database\Schema\Blueprint;     // Importa a classe que ajuda a definir colunas da tabela
use Illuminate\Support\Facades\Schema;        // Importa a fachada Schema para criar/alterar tabelas

// Declara uma nova classe de migração chamada CreateEmpresasTable
return new class extends Migration
{
    // Método que será executado quando rodar "php artisan migrate"
    public function up(): void
    {
        // Cria a tabela 'empresas' no banco de dados
        Schema::create('empresas', function (Blueprint $table) {
            // Cria a coluna 'id' como chave primária auto incremental (bigint)
            $table->id();
            // Cria a coluna 'nome' para guardar o nome da empresa
            $table->string('nome');
            // Cria as colunas 'created_at' e 'updated_at' automaticamente
            $table->timestamps();
        });
    }

    // Método que será executado quando rodar "php artisan migrate:rollback"
    public function down(): void
    {
        // Remove a tabela 'empresas' caso exista
        Schema::dropIfExists('empresas');
    }
};
