<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidQuizResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'question_id',
        'selected_answer',
        'earned_coin',
    ];
}
