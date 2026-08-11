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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id');
            $table->foreignId('user_id');
            $table->string('patient_name');
            $table->string('patient_email');
            $table->dateTime('scheduled_at');
            $table->text('reason')->nullable();
            $table->string('status')->default('booked');
            $table->boolean('reminder_opt_in')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->unique(['doctor_id', 'scheduled_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
