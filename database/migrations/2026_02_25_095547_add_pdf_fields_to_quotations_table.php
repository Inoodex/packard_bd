<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('client_name')->nullable();
            $table->string('client_designation')->nullable();
            $table->text('client_address')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            $table->string('attention_to')->nullable();
            $table->text('body_content')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('subject')->nullable();
            $table->string('company_name')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_designation')->nullable();
            $table->string('signatory_photo')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->text('company_address')->nullable();
            $table->text('additional_enclosed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            //
        });
    }
};
