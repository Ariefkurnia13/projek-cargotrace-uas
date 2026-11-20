<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 40)->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('restrict');
            $table->foreignId('origin_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('destination_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('current_location_id')->nullable()->constrained('locations')->onDelete('set null');

            $table->string('cargo_type', 60)->default('GENERAL');
            $table->decimal('total_weight_kg', 10, 2)->default(0.00);
            $table->decimal('total_volume_cbm', 10, 2)->default(0.00);
            
            // Status Lifecycle (Slide 06)
            $table->string('current_status', 30)->default('CREATED')->index();

            // Jadwal SLA & Waktu Tempuh
            $table->dateTime('expected_departure');
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('expected_arrival');
            $table->dateTime('estimated_arrival');
            $table->dateTime('actual_arrival')->nullable();

            // Kolom Kalkulasi Delay & SLA
            $table->boolean('is_delayed')->default(false)->index();
            $table->integer('delay_duration_minutes')->default(0);
            $table->string('delay_severity', 20)->default('NONE')->index();
            $table->string('delay_reason', 255)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite Index untuk optimasi pencarian & dashboard
            $table->index(['current_status', 'is_delayed']);
            $table->index(['customer_id', 'current_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};