<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vonage_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number', 15);
            $table->text('sms_body');
            $table->string('message_id', 100);
            $table->date('sent_at')->nullable();
            $table->date('delivered_at')->nullable();
            $table->date('abandoned_at')->nullable();
            $table->string('abandoned_reason', 200)->nullable();
            $table->smallInteger('retry_count')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vonage_sms_logs');
    }
};
