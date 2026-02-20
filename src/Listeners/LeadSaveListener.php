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
     * Handle the event.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    public function handle($lead)
    {
        // El plugin WooCommerce u otra fuente envia `custom_net_value` por EAV de Krayin
        // Revisamos si el lead actual tiene ese atributo
        if (isset($lead->custom_net_value)) {
            $netValue = $lead->custom_net_value;

            // Update directly to avoid recursion since we are in a lead.after save event
            \Illuminate\Support\Facades\DB::table('leads')
                ->where('id', $lead->id)
                ->update(['net_value' => $netValue]);
        }
    }
}
