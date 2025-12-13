<?php
// Abre a tag PHP
use Illuminate\Database\Migrations\Migration; // Importa classe de migração
use Illuminate\Database\Schema\Blueprint;     // Importa Blueprint para definição das colunas
use Illuminate\Support\Facades\Schema;        // Importa Schema para criar a tabela

// Declara a classe de migração anônima
return new class extends Migration
{
    // Método chamado ao rodar "php artisan migrate"
    public function up(): void
    {
        // Cria a tabela 'daily_requests'
        Schema::create('daily_requests', function (Blueprint $table) {
            // Cria coluna 'id' como chave primária
            $table->id();
            // Cria coluna 'user_id' para vincular a requisição ao funcionário
            $table->unsignedBigInteger('user_id');
            // Cria coluna 'empresa_id' para vincular a requisição à empresa
            $table->unsignedBigInteger('empresa_id');
            // Cria coluna 'data_diaria' para armazenar o dia solicitado
            $table->date('data_diaria');
            // Cria coluna 'status' para armazenar o estado da requisição (pendente, aprovado, recusado)
            $table->string('status')->default('pendente');
            // Cria coluna 'observacoes' para guardar observações do funcionário ou da empresa
            $table->text('observacoes')->nullable();
            // Cria coluna 'aprovado_por' para guardar o ID do usuário que aprovou/recusou
            $table->unsignedBigInteger('aprovado_por')->nullable();
            // Cria colunas 'created_at' e 'updated_at'
            $table->timestamps();

            // Define a chave estrangeira de 'user_id' para a tabela 'users'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Define a chave estrangeira de 'empresa_id' para a tabela 'empresas'
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            // Define a chave estrangeira de 'aprovado_por' para a tabela 'users'
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    // Método chamado ao rodar "php artisan migrate:rollback"
    public function down(): void
    {
        // Remove a tabela 'daily_requests' caso exista
        Schema::dropIfExists('daily_requests');
    }
};
