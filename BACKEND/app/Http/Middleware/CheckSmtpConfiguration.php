<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class CheckSmtpConfiguration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if SMTP settings are available in the configuration
        $smtpHost = Config::get('mail.host');
        $smtpPort = Config::get('mail.port');
        $smtpUsername = Config::get('mail.username');
        $smtpPassword = Config::get('mail.password');

        // Ensure SMTP settings are not empty
        if (empty($smtpHost) || empty($smtpPort) || empty($smtpUsername) || empty($smtpPassword)) {
            return response()->json([
                'error' => 'SMTP configuration is incomplete.',
                'details' => 'Please check your .env file or the mail configuration settings.',
            ], 400); // Return a 400 Bad Request response
        }

        // Optional: Test mail send to ensure SMTP is functioning
        try {
            Mail::raw('SMTP Configuration Test', function ($message) {
                $message->to('test@example.com')
                        ->subject('SMTP Test Email');
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send a test email.',
                'details' => $e->getMessage(),
            ], 500); // Return 500 Internal Server Error if mail fails
        }

        // Proceed with the request if everything is okay
        return $next($request);
    }
}
