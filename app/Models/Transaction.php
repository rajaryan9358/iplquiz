<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'txn_amount',
        'txn_title',
        'txn_id',
        'txn_message',
        'txn_mode',
        'txn_status',
        'payu_money_id',
    ];
}
