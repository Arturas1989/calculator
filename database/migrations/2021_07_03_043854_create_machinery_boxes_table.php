<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineryBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machinery_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machinery_id');
            $table->foreign('machinery_id')->references('id')->on('machineries');
            $table->unsignedBigInteger('box_id');
            $table->foreign('box_id')->references('id')->on('boxes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machinery_boxes');
    }
}
