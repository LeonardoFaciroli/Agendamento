<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adiciona o qr_token somente se ainda não existir
            if (!Schema::hasColumn('users', 'qr_token')) {
                $table->string('qr_token', 100)
                    ->unique()
                    ->nullable()
                    ->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'qr_token')) {
                $table->dropColumn('qr_token');
            }
        });
    }
};
