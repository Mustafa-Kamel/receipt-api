<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('valid_from');
            $table->timestamp('valid_to');
            $table->nullableMorphs('applied_on');
            $table->unsignedSmallInteger('count_range_min');
            $table->unsignedSmallInteger('count_range_max')->nullable()->default(65535);
            $table->nullableMorphs('dixcount_on');
            $table->enum('discount_type', ['FIXED', 'PERCENT'])->default('FIXED');
            $table->decimal('dicount_value')->nullable()->default(0.0);
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
        Schema::dropIfExists('offers');
    }
}
