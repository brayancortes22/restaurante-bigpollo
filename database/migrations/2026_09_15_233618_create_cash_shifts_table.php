<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cajero responsable
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_amount', 12, 2); // Base inicial en efectivo
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('card_sales', 12, 2)->default(0);
            $table->decimal('transfer_sales', 12, 2)->default(0); // Nequi, Daviplata, Bancolombia
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0); // Base + Ventas en efectivo
            $table->decimal('actual_cash_counted', 12, 2)->nullable(); // Lo que el cajero cuenta al final
            $table->decimal('difference', 12, 2)->nullable(); // Sobrante (+) o Faltante (-)
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
