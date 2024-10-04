<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up()
{
    Schema::create('tenants', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('db_name');        // Tenant's database name
        $table->string('db_host')->default('127.0.0.1');
        $table->string('db_port')->default('3306');
        $table->string('db_username')->default('root');
        $table->string('db_password')->nullable();
        $table->nullableTimestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('tenants');
    }
}
