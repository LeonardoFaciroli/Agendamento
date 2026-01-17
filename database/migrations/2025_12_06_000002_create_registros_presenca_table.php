<?php

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

            $table->date('data_presenca');

            $table->time('hora_entrada')->nullable();
            $table->time('hora_saida')->nullable();

            $table->decimal('horas_trabalhadas', 5, 2)->nullable();

            $table->enum('status_presenca', ['presente', 'ausente', 'falta_justificada'])
                ->default('presente');

            $table->enum('status_pagamento', ['pendente', 'pago'])
                ->default('pendente');

            $table->decimal('valor_diaria', 10, 2)->nullable();
            $table->dateTime('data_pagamento')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['user_id', 'data_presenca']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_presenca');
    }
};
