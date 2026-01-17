<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'invitation_token') && Schema::hasTable('convites')) {
            $tokens = DB::table('users')
                ->whereNotNull('invitation_token')
                ->get(['id', 'invitation_token']);

            if ($tokens->isNotEmpty()) {
                $now = now();
                $rows = $tokens->map(function ($tokenRow) use ($now) {
                    return [
                        'user_id' => $tokenRow->id,
                        'token' => $tokenRow->invitation_token,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                DB::table('convites')->insertOrIgnore($rows);
            }
        }

        if (Schema::hasColumn('users', 'invitation_token')) {
            $this->dropUniqueIndexIfExists('users', 'users_invitation_token_unique');
        }

        if (Schema::hasColumn('users', 'qr_token')) {
            $this->dropUniqueIndexIfExists('users', 'users_qr_token_unique');
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'invitation_token')) {
                $table->dropColumn('invitation_token');
            }

            if (Schema::hasColumn('users', 'qr_token')) {
                $table->dropColumn('qr_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'invitation_token')) {
                $table->string('invitation_token', 120)
                    ->nullable()
                    ->unique()
                    ->after('remember_token');
            }

            if (! Schema::hasColumn('users', 'qr_token')) {
                $table->string('qr_token', 100)
                    ->unique()
                    ->nullable()
                    ->after('id');
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
