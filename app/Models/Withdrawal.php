<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['user_id', 'currency', 'amount', 'bep20_address', 'status'];

    protected $casts = [
        'amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
