<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // ← tambahkan unique
            $table->string('kota');
            $table->string('kecamatan');
            $table->string('kelurahan');
            $table->text('address');
            $table->string('maps')->nullable();
            $table->string('contact')->nullable();
            $table->enum('status', ['pending', 'onprogress'])->default('pending');
            $table->string('pic')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
