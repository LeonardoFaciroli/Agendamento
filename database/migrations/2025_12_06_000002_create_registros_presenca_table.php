<?php

// database/migrations/2025_12_06_000002_create_registros_presenca_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registros_presenca', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id')->nullable();

            // Um registro por dia por funcionário
            $table->date('data_presenca');

            // Apenas hora/minuto/segundo
            $table->time('hora_entrada')->nullable();
            $table->time('hora_saida')->nullable();

            // Horas trabalhadas no dia (ex.: 7.50, 8.00 etc.)
            $table->decimal('horas_trabalhadas', 5, 2)->nullable();

            // presente, ausente, falta_justificada etc.
            $table->enum('status_presenca', ['presente', 'ausente', 'falta_justificada'])
                ->default('presente');

            // pendente, pago
            $table->enum('status_pagamento', ['pendente', 'pago'])
                ->default('pendente');

            $table->decimal('valor_diaria', 10, 2)->nullable();
            $table->dateTime('data_pagamento')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            // Se tiver tabela empresas, descomente:
            // $table->foreign('empresa_id')->references('id')->on('empresas');

            // Garante 1 registro por funcionário por dia
            $table->unique(['user_id', 'data_presenca']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_presenca');
    }
};
