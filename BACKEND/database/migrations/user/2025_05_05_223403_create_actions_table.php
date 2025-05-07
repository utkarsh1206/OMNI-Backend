<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();       // e.g. "view", "create", "edit", "delete"
            $table->string('display_name');
            $table->timestamps();
        });

        // Seed default actions
        DB::table('actions')->insert([
            ['name' => 'view', 'display_name' => 'View'],
            ['name' => 'create', 'display_name' => 'Create'],
            ['name' => 'edit', 'display_name' => 'Edit'],
            ['name' => 'delete', 'display_name' => 'Delete'],
            ['name' => 'download', 'display_name' => 'Download'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
