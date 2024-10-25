<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'amount',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    protected static function booted()
    {
        static::created(function ($donation) {
            $campaign = $donation->campaign;
            $campaign->collected_amount = $campaign->donations()->where('status', 'completed')->sum('amount');
            $campaign->save();
        });
    }
}
