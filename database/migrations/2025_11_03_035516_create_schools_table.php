<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // ID
            $table->string('name'); // Name
            $table->string('kota'); // Kota
            $table->string('kecamatan'); // Kecamatan
            $table->string('kelurahan'); // Kelurahan 
            $table->text('address'); // Address
            $table->string('maps')->nullable(); // Maps (link Google Maps, optional)
            $table->string('contact')->nullable(); // Contact (telepon/email, optional)
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status
            $table->string('pic')->nullable(); // PIC (penanggung jawab)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
