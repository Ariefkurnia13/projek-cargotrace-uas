<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('item_name', 150);
            $table->string('sku', 60)->nullable();
            $table->string('package_type', 40)->default('PALLET'); // CONTAINER, PALLET, CARTON
            $table->integer('quantity')->default(1);
            $table->decimal('weight_kg', 10, 2)->default(0.00);
            $table->decimal('volume_cbm', 10, 2)->default(0.00);
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};