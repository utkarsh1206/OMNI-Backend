<?php 
namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\CronCommand;
use File;
use Log;
use Carbon\Carbon;

class CommandSignatureServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Listen for CommandStarting event
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            // Check if the command being executed is 'make:command'
            if ($event->command === 'make:command') {
                // Get the name of the new command being generated
                $commandName = $event->input->getArgument('name');
                // Run the Artisan make:command process
                $this->createCommandAndStoreSignature($commandName);
            }
        }); 
    }


    /**
     * Method to generate the command and store the signature.
     *
     * @param string $commandName
     */
    public function createCommandAndStoreSignature(string $commandName)
    {
        // Generate the new command using Artisan
        $commandPath = app_path("Console/Commands/{$commandName}.php");
        // Ensure the file exists after command generation
        if (File::exists($commandPath)) {
            // Read the content of the command file
            $fileContents = File::get($commandPath);
            // Use regex to extract the signature
            preg_match('/protected \$signature = \'([^\']+)\'/', $fileContents, $matches);
            if (isset($matches[1])) {
                $signature = $matches[1];
                // Store the signature in the 'cron_commands' table
                if (strpos($signature, 'cron') !== false) {
                    $type = 'other';
                } else {
                    $type = 'cron';
                }
                CronCommand::create([
                    'command_signature' => $signature,
                    'type' => $type,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                Log::info("Command '$commandName' created with signature '$signature' and stored in the database.");
            } else {
                Log::error("Could not extract signature from the generated command: '$commandName'.");
            }
        } else {
            Log::error("The generated command file for '$commandName' was not found.");
        }
    }
}
