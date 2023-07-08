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
        Schema::create('room_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('confirmationNumber');
            $table->string('headGuest');
            $table->string('email');
            $table->string('contact');
            $table->unsignedBigInteger('roomId');
            $table->date('checkInDate');
            $table->date('checkOutDate');
            $table->unsignedInteger('guests');
            $table->text('specialRequests')->nullable();
            $table->boolean('isConfirmed')->default(false);
            $table->string('paymentId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_reservations');
    }
};
