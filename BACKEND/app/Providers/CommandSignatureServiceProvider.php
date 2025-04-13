<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\CronCommand;
use File;
use Carbon\Carbon;

class CommandSignatureServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Listen for CommandStarting event
        Event::listen(CommandFinished::class, function ($event) {
            // Log the event to check if it's being triggered
            generateLogs('info', 'Command Starting event fired!');
            // Check if the command being executed is 'make:command'
            if ($event->command === 'make:command') {
                $commandName = $event->input->getArgument('name');
                generateLogs('info', 'Artisan make:command executed for ' . $commandName);
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
                $this->updateSeederFile($signature, $type);
                generateLogs('info', "Command '$commandName' created with signature '$signature' and stored in the database.");
            } else {
                generateLogs('error', "Could not extract signature from the generated command: '$commandName'.");
            }
        } else {
            generateLogs('error', "The generated command file for '$commandName' was not found.");
        }
    }


    public function updateSeederFile(string $signature, $type)
    {
        $seederFilePath = database_path('seeders/CronCommandsSeeder.php');

        if (File::exists($seederFilePath)) {
            // Read the existing seeder file content
            $fileContents = File::get($seederFilePath);

            // Check if the signature is already in the seeder file to avoid duplicates
            if (strpos($fileContents, $signature) === false) {
                // Prepare the new line to add to the seeder
                $newLine = "\n\t\t['command_signature' => '{$signature}', 'type' => '{$type}'],";
                // Find the place where the cronCommands array is declared
                $pattern = '/\$cronCommands = \[(.*?)\];/s';
                $replacement = '$cronCommands = [${1}' . $newLine . "\n\t];";
                $newContents = preg_replace($pattern, $replacement, $fileContents);
                // Write the updated contents back to the seeder file
                File::put($seederFilePath, $newContents);

                generateLogs('info', "CronCommandsSeeder file has been updated with signature: '$signature'.");
            } else {
                generateLogs('info', "Command signature '$signature' already exists in the seeder file.");
            }
        } else {
            generateLogs('error', "Seeder file 'CronCommandsSeeder.php' does not exist.");
        }
    }
}
