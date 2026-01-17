<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diaristas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();
            $table->foreignId('filial_id')
                ->nullable()
                ->constrained('filiais')
                ->nullOnDelete();
            $table->string('nome');
            $table->string('cpf', 20)->nullable()->unique();
            $table->string('telefone', 30)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('funcao', 120)->nullable();
            $table->string('pix', 255)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diaristas');
    }
};
