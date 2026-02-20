<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('net_value', 12, 4)->nullable()->after('lead_value');
        });
        
        // Populate net_value from EAV attribute 'custom_net_value' if it exists.
        // We first find the attribute ID for custom_net_value.
        $attribute = DB::table('attributes')->where('code', 'custom_net_value')->where('entity_type', 'leads')->first();
        
        if ($attribute) {
            // Get all values for this attribute
            $values = DB::table('attribute_values')
                ->where('attribute_id', $attribute->id)
                ->where('entity_type', 'leads')
                ->get();
                
            foreach ($values as $valueRow) {
                // The value is stored in one of the columns (text_value, float_value, integer_value) depending on type.
                // Assuming it's a price/decimal, it's likely in float_value or text_value.
                $netValue = $valueRow->float_value ?? $valueRow->text_value ?? $valueRow->integer_value;
                if ($netValue !== null) {
                    DB::table('leads')
                        ->where('id', $valueRow->entity_id)
                        ->update(['net_value' => $netValue]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('net_value');
        });
    }
};
