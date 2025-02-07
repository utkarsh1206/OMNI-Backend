<?php

use App\Models\EmailLog;
use App\Models\EmailConfigure;

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

if (! function_exists('setEmailConfig')) {

function setEmailConfig()
{
    $emailConfig = EmailConfigure::where('type', 'gmail')->first();
    $smtpHost = $emailConfig->smtp_host;
    $smtpPort = $emailConfig->smtp_port;
    $smtpUser = $emailConfig->smtp_user;
    $smtpPassword = $emailConfig->smtp_password;
    $sender_name = $emailConfig->sender_name;
    $sender_email = $emailConfig->sender_email;

    config([
        'mail.mailers.smtp.host' => $smtpHost,
        'mail.mailers.smtp.port' => $smtpPort,
        'mail.mailers.smtp.username' => $smtpUser,
        'mail.mailers.smtp.password' => $smtpPassword,
        'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
        'mail.from.address' =>  $sender_email,
        'mail.from.name' => $sender_name,
    ]);
}


}