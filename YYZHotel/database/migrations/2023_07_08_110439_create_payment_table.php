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
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->timestamp('paymentTransactionDate')->useCurrent();
            $table->decimal('paymentAmount', 10, 2);
            $table->string('confirmationNumber');
            $table->string('paymentCurrency');
            $table->string('paymentType');
            $table->string('paymentStatus');
            $table->string('paymentTransactionId');
            $table->string('paymentMethod');
            $table->string('paymentGatewayProcessor');
            $table->text('paymentNoteComments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
