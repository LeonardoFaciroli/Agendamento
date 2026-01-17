<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf', 20)
                    ->nullable()
                    ->unique()
                    ->after('email');
            }

            if (! Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 30)
                    ->nullable()
                    ->after('cpf');
            }

            if (! Schema::hasColumn('users', 'endereco')) {
                $table->string('endereco')
                    ->nullable()
                    ->after('telefone');
            }

            if (! Schema::hasColumn('users', 'filial_id')) {
                $table->unsignedBigInteger('filial_id')
                    ->nullable()
                    ->after('empresa_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'filial_id')) {
                $table->foreign('filial_id')
                    ->references('id')
                    ->on('filiais')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'filial_id')) {
                $table->dropForeign(['filial_id']);
                $table->dropColumn('filial_id');
            }

            if (Schema::hasColumn('users', 'endereco')) {
                $table->dropColumn('endereco');
            }

            if (Schema::hasColumn('users', 'telefone')) {
                $table->dropColumn('telefone');
            }

            if (Schema::hasColumn('users', 'cpf')) {
                $table->dropUnique(['cpf']);
                $table->dropColumn('cpf');
            }
        });
    }
};
