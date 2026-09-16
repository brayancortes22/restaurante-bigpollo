<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->float('pos_x')->nullable()->after('location');
            $table->float('pos_y')->nullable()->after('pos_x');
            $table->string('shape')->default('square')->after('pos_y');
            $table->string('zone')->default('calle')->after('shape');
            $table->foreignId('merged_with_table_id')
                ->nullable()
                ->after('zone')
                ->constrained('restaurant_tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropForeign(['merged_with_table_id']);
            $table->dropColumn(['pos_x', 'pos_y', 'shape', 'zone', 'merged_with_table_id']);
        });
    }
};
