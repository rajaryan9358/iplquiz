<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaidQuizResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paid_quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->integer('user_id');
            $table->integer('question_id');
            $table->string('selected_answer');
            $table->integer('earned_coin')->default(0);
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
        Schema::dropIfExists('paid_quiz_responses');
    }
}
