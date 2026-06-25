<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds branch/variation/order_type/usd fields to the leads table
     * for the Tareas 2.0 CRM features (financial reports filtering).
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Sucursal: online, san_cosmos, tatakualab, other_pos
            $table->string('branch', 30)->nullable()->after('net_value');
            
            // Variación: online, en_puerta, cortesia, programacion_especial
            $table->string('sale_variation', 30)->nullable()->after('branch');
            
            // Tipo de orden: tickets, merchandise, mixed
            $table->string('order_type', 20)->nullable()->after('sale_variation');
            
            // Tipo de cambio PYG/USD vigente al momento de la venta
            $table->decimal('usd_rate', 16, 4)->nullable()->after('order_type');
            
            // Total en USD
            $table->decimal('total_usd', 12, 4)->nullable()->after('usd_rate');
            
            // IDs de productos WC (comma-separated para matching con tags en Krayin)
            $table->text('wc_product_ids')->nullable()->after('total_usd');

            // Indexes for filtering performance
            $table->index('branch');
            $table->index('sale_variation');
            $table->index('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['branch']);
            $table->dropIndex(['sale_variation']);
            $table->dropIndex(['order_type']);
            $table->dropColumn([
                'branch',
                'sale_variation',
                'order_type',
                'usd_rate',
                'total_usd',
                'wc_product_ids',
            ]);
        });
    }
};
