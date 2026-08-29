<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MmfInterestLog extends Model
{
    protected $fillable = ['user_id', 'mmf_subscription_id', 'amount', 'paid_at'];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(MmfSubscription::class, 'mmf_subscription_id');
    }
}
