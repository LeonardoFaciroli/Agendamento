<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasTable('diaristas')) {
            $rows = DB::table('users')
                ->whereIn('role', ['funcionario', 'diarista'])
                ->get([
                    'id',
                    'empresa_id',
                    'filial_id',
                    'name',
                    'cpf',
                    'telefone',
                    'pix',
                    'endereco',
                    'created_at',
                    'updated_at',
                ]);

            if ($rows->isNotEmpty()) {
                $insert = $rows->map(function ($row) {
                    return [
                        'user_id' => $row->id,
                        'empresa_id' => $row->empresa_id,
                        'filial_id' => $row->filial_id,
                        'nome' => $row->name,
                        'cpf' => $row->cpf,
                        'telefone' => $row->telefone,
                        'pix' => $row->pix,
                        'endereco' => $row->endereco,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ];
                })->all();

                DB::table('diaristas')->insertOrIgnore($insert);
            }
        }

        if (Schema::hasColumn('users', 'cpf')) {
            $this->dropUniqueIndexIfExists('users', 'users_cpf_unique');
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cpf')) {
                $table->dropColumn('cpf');
            }

            if (Schema::hasColumn('users', 'telefone')) {
                $table->dropColumn('telefone');
            }

            if (Schema::hasColumn('users', 'pix')) {
                $table->dropColumn('pix');
            }

            if (Schema::hasColumn('users', 'endereco')) {
                $table->dropColumn('endereco');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf', 20)->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 30)->nullable()->after('cpf');
            }

            if (! Schema::hasColumn('users', 'pix')) {
                $table->string('pix', 255)->nullable()->after('telefone');
            }

            if (! Schema::hasColumn('users', 'endereco')) {
                $table->string('endereco')->nullable()->after('pix');
            }
        });
    }

    private function dropUniqueIndexIfExists(string $table, string $indexName): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS ' . $indexName);
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }
};
