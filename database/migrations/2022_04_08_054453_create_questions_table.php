<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->integer('question_number');
            $table->text('question');
            $table->string('option_one');
            $table->string('option_two');
            $table->string('option_three');
            $table->string('option_four');
            $table->string('option_five');
            $table->string('option_six');
            $table->string('option_seven');
            $table->string('option_eight');
            $table->integer('paid_question_point');
            $table->integer('paid_answer_multiple');
            $table->integer('free_question_point');
            $table->integer('free_answer_point');
            $table->string('correct_answer')->nullable();
            $table->integer('question_status')->default(0);
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
        Schema::dropIfExists('questions');
    }
}
