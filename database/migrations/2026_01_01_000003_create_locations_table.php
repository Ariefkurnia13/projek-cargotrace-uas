<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->index(); // UN/LOCODE e.g. IDJKT, SGSIN, NLRTM, DEHAM
            $table->string('name', 150);
            $table->string('city', 100);
            $table->string('country', 100);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('is_hub')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
