<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (! Schema::hasColumn('registros_presenca', 'filial_id')) {
                $table->unsignedBigInteger('filial_id')
                    ->nullable()
                    ->after('empresa_id');
            }
        });

        Schema::table('registros_presenca', function (Blueprint $table) {
            if (Schema::hasColumn('registros_presenca', 'filial_id')) {
                $table->foreign('filial_id')
                    ->references('id')
                    ->on('filiais')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (Schema::hasColumn('registros_presenca', 'filial_id')) {
                $table->dropForeign(['filial_id']);
                $table->dropColumn('filial_id');
            }
        });
    }
};
