<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('daily_shift_id')
                ->nullable()
                ->after('data_diaria');

            $table->foreign('daily_shift_id')
                ->references('id')
                ->on('daily_shifts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('daily_requests', function (Blueprint $table) {
            $table->dropForeign(['daily_shift_id']);
            $table->dropColumn('daily_shift_id');
        });
    }
};
