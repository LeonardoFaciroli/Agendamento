<?php

// Importa as classes necessárias para a migração
use Illuminate\Database\Migrations\Migration; // Classe base de migração
use Illuminate\Database\Schema\Blueprint;     // Classe para definir as colunas
use Illuminate\Support\Facades\Schema;        // Fachada para criar/alterar tabelas

// Retorna uma classe anônima que estende Migration
return new class extends Migration
{
    // Método que será executado ao rodar "php artisan migrate"
    public function up(): void
    {
        // Cria a tabela "users"
        Schema::create('users', function (Blueprint $table) {
            // Coluna "id" como chave primária auto-incremento
            $table->id();

            // Coluna "name" para o nome do usuário
            $table->string('name');

            // Coluna "email" com índice único (não pode repetir)
            $table->string('email')->unique();

            // Coluna opcional para data de verificação de e-mail
            $table->timestamp('email_verified_at')->nullable();

            // Coluna "password" para a senha (hash)
            $table->string('password');

            // Coluna "remember_token" usada pelo "lembrar-me" do Laravel
            $table->rememberToken();

            // Coluna "role" para indicar o tipo de usuário: "empresa" ou "funcionario"
            $table->string('role')->default('funcionario');

            // Coluna "empresa_id" para relacionar o usuário a uma empresa
            $table->unsignedBigInteger('empresa_id')->nullable();

            // Chave estrangeira: empresa_id -> empresas.id
            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');

            // Colunas "created_at" e "updated_at"
            $table->timestamps();
        });
    }

    // Método que será executado ao rodar "php artisan migrate:rollback"
    public function down(): void
    {
        // Remove a tabela "users" caso exista
        Schema::dropIfExists('users');
    }
};
