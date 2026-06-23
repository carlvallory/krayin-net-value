<?php

namespace CarlVallory\KrayinNetValue\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LeadSaveListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Mapeo de custom_attributes EAV → columnas directas en la tabla leads.
     * 
     * @var array
     */
    private $fieldMapping = [
        'custom_net_value'       => 'net_value',
        'branch'                 => 'branch',
        'sale_variation'         => 'sale_variation',
        'order_type'             => 'order_type',
        'usd_rate'               => 'usd_rate',
        'total_usd'              => 'total_usd',
        'wc_product_ids'         => 'wc_product_ids',
        'is_incomplete_purchase' => 'is_incomplete_purchase',
    ];

    /**
     * Handle the event.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    public function handle($lead)
    {
        $updates = [];

        // Mapear cada custom_attribute EAV a su columna directa en leads
        foreach ($this->fieldMapping as $eavField => $dbColumn) {
            if (isset($lead->{$eavField})) {
                $updates[$dbColumn] = $lead->{$eavField};
            }
        }

        // Update directly to avoid recursion since we are in a lead.after save event
        if (!empty($updates)) {
            \Illuminate\Support\Facades\DB::table('leads')
                ->where('id', $lead->id)
                ->update($updates);
        }
    }
}
