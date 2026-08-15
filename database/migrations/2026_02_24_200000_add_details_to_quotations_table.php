<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {

            $table->decimal('vat_percent', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_percent');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('vat_amount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            $table->decimal('installation_charge', 12, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'vat_percent',
                'vat_amount',
                'tax_percent',
                'tax_amount',
                'installation_charge',
            ]);
        });
    }
};
