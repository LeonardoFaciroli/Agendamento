<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        if (Schema::hasTable('users')) {
            $rows = DB::table('users')
                ->where('role', 'admin')
                ->get(['id']);

            if ($rows->isNotEmpty()) {
                $now = now();
                $insert = $rows->map(function ($row) use ($now) {
                    return [
                        'user_id' => $row->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                DB::table('admins')->insertOrIgnore($insert);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
