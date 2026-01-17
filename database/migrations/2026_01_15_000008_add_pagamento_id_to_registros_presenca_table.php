<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (! Schema::hasColumn('registros_presenca', 'pagamento_id')) {
                $table->unsignedBigInteger('pagamento_id')
                    ->nullable()
                    ->after('status_pagamento');
            }
        });

        Schema::table('registros_presenca', function (Blueprint $table) {
            if (Schema::hasColumn('registros_presenca', 'pagamento_id')) {
                $table->foreign('pagamento_id')
                    ->references('id')
                    ->on('pagamentos')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registros_presenca', function (Blueprint $table) {
            if (Schema::hasColumn('registros_presenca', 'pagamento_id')) {
                $table->dropForeign(['pagamento_id']);
                $table->dropColumn('pagamento_id');
            }
        });
    }
};
