<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (! Schema::hasColumn('registros_presenca', 'horas_trabalhadas')) {
                $table->decimal('horas_trabalhadas', 5, 2)->nullable()->after('hora_saida');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (Schema::hasColumn('registros_presenca', 'horas_trabalhadas')) {
                $table->dropColumn('horas_trabalhadas');
            }
        });
    }
};
