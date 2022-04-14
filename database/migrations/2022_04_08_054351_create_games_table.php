<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('game_name');
            $table->string('game_image')->nullable();
            $table->string('game_teams');
            $table->string('team_one_name');
            $table->string('team_two_name');
            $table->string('team_one_image')->nullable();
            $table->string('team_two_image')->nullable();
            $table->integer('prediction_fee');
            $table->integer('game_status')->default(0);
            $table->string('winner_team')->nullable();
            $table->integer('paid_prediction_multiple')->default(1);
            $table->integer('free_prediction_amount')->default(0);
            $table->integer('paid_winner_count')->default(0);
            $table->integer('free_winner_count')->default(0);
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
        Schema::dropIfExists('games');
    }
}
