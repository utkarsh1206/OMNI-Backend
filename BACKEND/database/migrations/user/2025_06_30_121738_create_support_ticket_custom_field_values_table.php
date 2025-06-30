<?php

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
        Schema::create('support_ticket_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')
                ->constrained('support_tickets');
            $table->unsignedBigInteger('product_id');
            $table->string('field_name', 255);
            $table->text('field_value')->nullable();
            $table->timestamps();

            $table->unique(['support_ticket_id', 'product_id', 'field_name'], 'ticket_product_field_unique');

            $table->foreign(['product_id', 'field_name'])
                ->references(['product_id', 'field_name'])
                ->on('product_custom_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_custom_field_values');
    }
};
