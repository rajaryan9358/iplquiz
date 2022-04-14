<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_name',
        'game_image',
        'game_teams',
        'team_one_name',
        'team_two_name',
        'team_one_image',
        'team_two_image',
        'prediction_fee',
        'game_status',
        'winner_team',
        'paid_prediction_multiple',
        'free_prediction_amount',
        'paid_winner_count',
        'free_winner_count',
    ];
}
