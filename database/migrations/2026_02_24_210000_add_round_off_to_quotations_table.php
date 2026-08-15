<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('quotations', 'round_off')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->decimal('round_off', 12, 2)->default(0)->after('installation_charge');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quotations', 'round_off')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('round_off');
            });
        }
    }
};
