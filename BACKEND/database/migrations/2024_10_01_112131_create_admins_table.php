<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id('id'); // Equivalent to INT(11) with auto-increment
            $table->string('edit_id', 255);
            $table->string('username', 255);
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email', 255);
            $table->unsignedBigInteger('mobile'); // Equivalent to BIGINT(20)
            $table->string('password', 255);
            $table->string('otp', 255)->nullable(); // Can be NULL
            $table->enum('admin_type', ['Super', 'Simple'])->default('Simple');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('is_delete', ['Yes', 'No'])->default('No');
            $table->timestamps(); // This will create created_at and updated_at fields
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('modified')->default(DB::raw('CURRENT_TIMESTAMP')); // Date and time for MODIFIED
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
