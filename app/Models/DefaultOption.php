<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_one',
        'option_two',
        'option_three',
        'option_four',
        'option_five',
        'option_six',
        'option_seven',
        'option_eight',
    ];
}
