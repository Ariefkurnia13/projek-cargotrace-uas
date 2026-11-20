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

            $table->string('event_status', 30)->index(); // Status shipment pada saat event terjadi
            $table->string('event_type', 30)->default('NORMAL')->index(); // NORMAL, CHECKPOINT, DEPARTURE, ARRIVAL, EXCEPTION
            $table->dateTime('event_time')->index(); // Kunci pengurutan kronologis

            $table->string('location_name', 150);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('description');
            $table->foreignId('exception_id')->nullable()->constrained('shipment_exceptions')->onDelete('set null');

            $table->timestamps();

            // Compound index untuk kecepatan query timeline kronologis
            $table->index(['shipment_id', 'event_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};