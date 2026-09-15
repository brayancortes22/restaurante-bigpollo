<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('table_number');
            $table->integer('capacity')->default(4);
            $table->string('location')->nullable(); // 'salon_principal', 'terraza', 'segundo_piso'
            $table->enum('status', ['available', 'occupied', 'billed', 'reserved'])->default('available');
            $table->timestamps();

            $table->unique(['tenant_id', 'table_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
