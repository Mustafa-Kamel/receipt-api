<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('price');
            $table->float('weight')->nullable();
            $table->unsignedSmallInteger('in_stock')->default(65535);
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('country_id');
            $table->foreign('type_id')->references('id')->on('itemtypes')->onDelete('CASCADE');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('CASCADE');
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
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign('items_country_id_foreign');
            $table->dropForeign('items_type_id_foreign');
        });
        Schema::dropIfExists('items');
    }
}
