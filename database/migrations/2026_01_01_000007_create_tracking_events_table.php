<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            $table->string('event_status', 30)->index(); // Current shipment status at this event
            $table->string('event_type', 30)->default('NORMAL')->index(); // NORMAL, EXCEPTION, CHECKPOINT, DEPARTURE, ARRIVAL
            $table->dateTime('event_time')->index(); // Crucial for chronological ordering

            $table->string('location_name', 150);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('description');
            $table->foreignId('exception_id')->nullable()->constrained('shipment_exceptions')->onDelete('set null');

            $table->timestamps();

            // Compound index for fast chronological timeline retrieval
            $table->index(['shipment_id', 'event_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
