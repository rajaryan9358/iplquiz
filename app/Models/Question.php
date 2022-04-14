<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'question_number',
        'question',
        'option_one',
        'option_two',
        'option_three',
        'option_four',
        'option_five',
        'option_six',
        'option_seven',
        'option_eight',
        'paid_question_point',
        'paid_answer_multiple',
        'free_question_point',
        'free_answer_point',
        'correct_answer',
        'question_status',
    ];
}
