<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Create custom_net_value attribute for leads if it doesn't exist
        $netValueExists = DB::table('attributes')
            ->where('code', 'custom_net_value')
            ->where('entity_type', 'leads')
            ->exists();

        if (!$netValueExists) {
            DB::table('attributes')->insert([
                'code'            => 'custom_net_value',
                'name'            => 'Valor Neto',
                'type'            => 'price',
                'entity_type'     => 'leads',
                'is_required'     => 0,
                'is_unique'       => 0,
                'quick_add'       => 1,
                'is_user_defined' => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // 2. Create ruc_ci attribute for leads if it doesn't exist
        $rucExists = DB::table('attributes')
            ->where('code', 'ruc_ci')
            ->where('entity_type', 'leads')
            ->exists();

        if (!$rucExists) {
            DB::table('attributes')->insert([
                'code'            => 'ruc_ci',
                'name'            => 'RUC / CI',
                'type'            => 'text',
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
        // No rollback as requested by the user
    }
};
