<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('exception_type', 40)->index(); // CUSTOMS_HOLD, WEATHER_DELAY, PORT_CONGESTION, DAMAGED, MISROUTED, DOCUMENTATION_ISSUE
            $table->string('status', 30)->default('OPEN')->index(); // OPEN, INVESTIGATING, RESOLVED
            $table->string('severity', 20)->default('MEDIUM')->index(); // LOW, MEDIUM, HIGH

            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();

            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('impact_delay_hours', 8, 2)->default(0.00);

            $table->timestamps();

            $table->index(['shipment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_exceptions');
    }
};
