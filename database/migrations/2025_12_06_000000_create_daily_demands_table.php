<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_demands', function (Blueprint $table) {
            $table->id();
            $table->date('data_diaria');
            $table->unsignedInteger('qtd_funcionarios'); // quantos funcionários são necessários neste dia
            $table->timestamps();

            // Garante que só exista uma demanda por dia
            $table->unique('data_diaria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_demands');
    }
};
