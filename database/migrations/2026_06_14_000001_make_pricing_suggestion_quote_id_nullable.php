<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_suggestions', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
            $table->foreignId('quote_id')->nullable()->change();
            $table->foreign('quote_id')->references('id')->on('quotes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pricing_suggestions', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
            $table->foreignId('quote_id')->nullable(false)->change();
            $table->foreign('quote_id')->references('id')->on('quotes')->cascadeOnDelete();
        });
    }
};
