<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (! Schema::hasColumn('empresas', 'billing_status')) {
                $table->string('billing_status', 20)
                    ->default('past_due')
                    ->after('nome');
            }

            if (! Schema::hasColumn('empresas', 'paid_until')) {
                $table->date('paid_until')
                    ->nullable()
                    ->after('billing_status');
            }

            if (! Schema::hasColumn('empresas', 'mercadopago_preapproval_id')) {
                $table->string('mercadopago_preapproval_id', 120)
                    ->nullable()
                    ->unique()
                    ->after('paid_until');
            }

            if (! Schema::hasColumn('empresas', 'mercadopago_payer_id')) {
                $table->string('mercadopago_payer_id', 120)
                    ->nullable()
                    ->after('mercadopago_preapproval_id');
            }

            if (! Schema::hasColumn('empresas', 'billing_plan')) {
                $table->string('billing_plan', 50)
                    ->default('mensal')
                    ->after('mercadopago_payer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'billing_plan')) {
                $table->dropColumn('billing_plan');
            }
            if (Schema::hasColumn('empresas', 'mercadopago_payer_id')) {
                $table->dropColumn('mercadopago_payer_id');
            }
            if (Schema::hasColumn('empresas', 'mercadopago_preapproval_id')) {
                $table->dropColumn('mercadopago_preapproval_id');
            }
            if (Schema::hasColumn('empresas', 'paid_until')) {
                $table->dropColumn('paid_until');
            }
            if (Schema::hasColumn('empresas', 'billing_status')) {
                $table->dropColumn('billing_status');
            }
        });
    }
};
