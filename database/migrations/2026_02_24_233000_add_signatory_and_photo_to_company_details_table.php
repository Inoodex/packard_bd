<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_details', function (Blueprint $table) {
            if (!Schema::hasColumn('company_details', 'signatory_name')) {
                $table->string('signatory_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('company_details', 'signatory_designation')) {
                $table->string('signatory_designation')->nullable()->after('signatory_name');
            }

            if (!Schema::hasColumn('company_details', 'photo')) {
                $table->string('photo')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_details', function (Blueprint $table) {
            if (Schema::hasColumn('company_details', 'photo')) {
                $table->dropColumn('photo');
            }

            if (Schema::hasColumn('company_details', 'signatory_designation')) {
                $table->dropColumn('signatory_designation');
            }

            if (Schema::hasColumn('company_details', 'signatory_name')) {
                $table->dropColumn('signatory_name');
            }
        });
    }
};

