<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MmfSubscription extends Model
{
    protected $fillable = ['user_id', 'amount', 'expected_interest', 'locked_at', 'unlocks_at', 'status'];

    protected $casts = [
        'amount' => 'float',
        'expected_interest' => 'float',
        'locked_at' => 'datetime',
        'unlocks_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
