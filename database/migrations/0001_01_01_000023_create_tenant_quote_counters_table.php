<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_quote_counters', function (Blueprint $table) {
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('seq')->default(0);
            $table->primary(['tenant_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_quote_counters');
    }
};
