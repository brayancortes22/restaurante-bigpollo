<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('restaurant_table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Mesero o cajero
            $table->string('order_number');
            $table->enum('type', ['dine_in', 'takeout', 'delivery'])->default('dine_in');
            $table->enum('status', ['pending', 'in_kitchen', 'ready', 'served', 'paid', 'cancelled'])->default('pending');
            
            // Datos del cliente (opcional para delivery / factura)
            $table->string('customer_name')->nullable();
            $table->string('customer_nit_cedula')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('delivery_address')->nullable();

            // Valores financieros
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_method')->nullable(); // 'cash', 'card', 'transfer', 'mixed'
            $table->timestamp('paid_at')->nullable();

            // Facturación Electrónica DIAN (Factus)
            $table->string('factus_bill_number')->nullable();
            $table->string('factus_cufe')->nullable();
            $table->text('factus_qr_url')->nullable();
            $table->enum('factus_status', ['not_sent', 'pending', 'sent_valid', 'error'])->default('not_sent');
            $table->text('factus_error_message')->nullable();

            // Auditoría de cancelación o edición
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
