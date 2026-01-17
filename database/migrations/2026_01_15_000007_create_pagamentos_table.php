<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->unsignedInteger('dias_pagos');
            $table->string('comprovante_path')->nullable();
            $table->dateTime('data_pagamento');
            $table->unsignedBigInteger('pago_por')->nullable();
            $table->timestamps();
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('pago_por')->references('id')->on('users')->nullOnDelete();
            }
            if (Schema::hasTable('filiais')) {
                $table->foreign('filial_id')->references('id')->on('filiais')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            if (Schema::hasTable('users')) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['pago_por']);
            }
            if (Schema::hasTable('filiais')) {
                $table->dropForeign(['filial_id']);
            }
        });

        Schema::dropIfExists('pagamentos');
    }
};
