<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_one');
            $table->string('option_two');
            $table->string('option_three');
            $table->string('option_four');
            $table->string('option_five');
            $table->string('option_six');
            $table->string('option_seven');
            $table->string('option_eight');
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
        Schema::dropIfExists('default_options');
    }
}
