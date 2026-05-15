<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 32);
            $table->string('status')->default('draft');
            $table->date('issued_at');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->unsignedTinyInteger('vat_rate')->default(23);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('internal_note')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->char('pdf_hash', 64)->nullable();
            $table->string('pdf_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id', 'number']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
