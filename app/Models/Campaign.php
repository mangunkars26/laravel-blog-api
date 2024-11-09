<?php

namespace App\Models;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'target_amount',
        'collected_amount',
        'deadline',
        'status'
    ];
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ]
        ];
    }

    protected static function booted()
    {
        static::updated(function ($campaign){
            $campaign->collected_amount = $campaign->donations()->where('status', 'completed')->sum('amount');
            $campaign->save();
        });
    }
}
