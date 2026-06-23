<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Columna física (espejo del atributo EAV `is_incomplete_purchase`) para
     * filtrar de forma rápida y confiable los leads de compras incompletas.
     * El valor se copia desde el EAV en LeadSaveListener. Default false: los
     * leads existentes y los manuales (no provenientes del plugin) cuentan como completos.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('is_incomplete_purchase')->default(false)->after('wc_product_ids');
            $table->index('is_incomplete_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['is_incomplete_purchase']);
            $table->dropColumn('is_incomplete_purchase');
        });
    }
};
