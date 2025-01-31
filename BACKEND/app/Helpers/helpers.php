<?php

use App\Models\EmailLog;

if (! function_exists('logEmail')) {
    /**
     * Format price with a currency symbol.
     *
     * @param  float  $price
     * @param  string  $currency
     * @return string
     */
    function logEmail($data,$newConnectionName = NULL) {
        $emailLog = new EmailLog;
        if($newConnectionName)
        {
            $emailLog->setConnection($newConnectionName); 
        }
       // dd($data);
        $emailLog->fill($data);
        $emailLog->save();
    }
}

