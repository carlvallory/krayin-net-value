<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea el atributo booleano `is_incomplete_purchase` para leads.
     * Marca los leads originados por órdenes WooCommerce NO completadas
     * (failed/cancelled/refunded/on-hold) para que Administración/Finanzas
     * los excluya y Marketing los segmente.
     * Decisión 2026-06-23 — ver ANALISIS-MARKETING-VS-ADMIN.md §6.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $exists = DB::table('attributes')
            ->where('code', 'is_incomplete_purchase')
            ->where('entity_type', 'leads')
            ->exists();

        if (!$exists) {
            DB::table('attributes')->insert([
                'code'            => 'is_incomplete_purchase',
                'name'            => 'Compra Incompleta',
                'type'            => 'boolean',
                'entity_type'     => 'leads',
                'is_required'     => 0,
                'is_unique'       => 0,
                'quick_add'       => 1,
                'is_user_defined' => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sin rollback, en línea con el resto de las migraciones del paquete.
    }
};
