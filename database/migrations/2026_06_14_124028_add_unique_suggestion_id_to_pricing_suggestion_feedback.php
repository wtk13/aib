<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_suggestion_feedback', function (Blueprint $table) {
            $table->unique('suggestion_id');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_suggestion_feedback', function (Blueprint $table) {
            $table->dropUnique(['suggestion_id']);
        });
    }
};
