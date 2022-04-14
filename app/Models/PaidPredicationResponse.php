<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidPredicationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'selected_team',
        'paid_amount',
    ];
}
