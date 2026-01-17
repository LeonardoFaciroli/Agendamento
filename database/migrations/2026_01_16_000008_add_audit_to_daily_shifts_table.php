<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_shifts', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_shifts', 'created_by')) {
                $table->unsignedBigInteger('created_by')
                    ->nullable()
                    ->after('vagas_totais');
            }

            if (! Schema::hasColumn('daily_shifts', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')
                    ->nullable()
                    ->after('created_by');
            }
        });

        Schema::table('daily_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('daily_shifts', 'created_by')) {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('daily_shifts', 'updated_by')) {
                $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('daily_shifts', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }

            if (Schema::hasColumn('daily_shifts', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
