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
        // Add a new column for unsignedBigInteger to store the ID if required
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('default_status_id')->nullable()->after('default_status');
        });

        // Migrate existing records to default_status_id if possible
        DB::table('support_tickets')->get()->each(function ($ticket) {
            if (is_numeric($ticket->default_status)) {
                // If it's an integer, directly use it in the new column
                DB::table('support_tickets')
                    ->where('id', $ticket->id)
                    ->update(['default_status_id' => (int)$ticket->default_status]);
            } else {
                // Assuming 'support_ticket_statuses' table exists and has 'name' and 'id' columns
                $status = DB::table('support_ticket_statuses')->where('name', $ticket->default_status)->first();

                if ($status) {
                    // If a corresponding ID is found, update the ticket with the mapped ID
                    DB::table('support_tickets')
                        ->where('id', $ticket->id)
                        ->update(['default_status_id' => $status->id]);
                } else {
                    // If no corresponding status is found, set the ID to NULL
                    DB::table('support_tickets')
                        ->where('id', $ticket->id)
                        ->update(['default_status_id' => null]);
                }
            }
        });

        // Optionally, drop the old `default_status` column if it's no longer needed
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('default_status');
        });

        // Rename the 'default_status_id' column to 'default_status'
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->renameColumn('default_status_id', 'default_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new column if rolling back the migration
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('default_status');
        });
    }
};
