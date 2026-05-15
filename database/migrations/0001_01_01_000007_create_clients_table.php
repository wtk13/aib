<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('nip', 10)->nullable();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->jsonb('custom_fields')->default('{}');
            $table->text('access_keys_encrypted')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'name']);
        });

        DB::statement('CREATE INDEX clients_custom_fields_gin ON clients USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
