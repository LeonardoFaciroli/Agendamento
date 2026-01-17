<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();
            $table->foreignId('filial_id')
                ->nullable()
                ->constrained('filiais')
                ->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::hasTable('users')) {
            $rows = DB::table('users')
                ->whereIn('role', ['porteiro', 'supervisor'])
                ->get(['id', 'empresa_id', 'filial_id']);

            if ($rows->isNotEmpty()) {
                $now = now();
                $insert = $rows->map(function ($row) use ($now) {
                    return [
                        'user_id' => $row->id,
                        'empresa_id' => $row->empresa_id,
                        'filial_id' => $row->filial_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                DB::table('supervisores')->insertOrIgnore($insert);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisores');
    }
};
