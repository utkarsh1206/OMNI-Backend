<?php
namespace App\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\CronCommand;
use File;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class CommandSignatureServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Listen for CommandStarting event
        Event::listen(CommandFinished::class, function ($event) {
            // Log the event to check if it's being triggered
            Log::info('CommandStarting event fired!');

            // Check if the command being executed is 'make:command'
            if ($event->command === 'make:command') {
                $commandName = $event->input->getArgument('name');
                Log::info('Artisan make:command executed for ' . $commandName);
                $this->delayedCreateCommandAndStoreSignature($commandName);
            }
        });
    }

    /**
     * Method to generate the command and store the signature after the command is created.
     *
     * @param string $commandName
     */
    public function delayedCreateCommandAndStoreSignature(string $commandName)
    {
        // Build the path of the generated command file
        $commandPath = app_path("Console" . DIRECTORY_SEPARATOR . "Commands" . DIRECTORY_SEPARATOR . "{$commandName}.php");

        // Ensure the file exists after command generation
        if (File::exists($commandPath)) {
            // Read the content of the command file
            $fileContents = File::get($commandPath);

            // Use regex to extract the signature
            preg_match('/protected \$signature = \'([^\']+)\'/', $fileContents, $matches);
            if (isset($matches[1])) {
                $signature = $matches[1];
                // Store the signature in the 'cron_commands' table
                $type = (strpos($signature, 'cron') !== false) ? 'cron' : 'other';
                CronCommand::updateOrCreate(
                    ['command_signature' => $signature],  // Search by command_signature
                    [
                        'command_signature' => $signature,
                        'type' => $type,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );

                Log::info("Command '$commandName' created with signature '$signature' and stored in the database.");
            } else {
                Log::error("Could not extract signature from the generated command: '$commandName'.");
            }
        } else {
            Log::error("The generated command file for '$commandName' was not found.");
        }
    }
}
