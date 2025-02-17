<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    // Mutator to format 'scheduled_at' before saving

    // Ensure 'scheduled_at' is cast to a DateTime object
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
    public function setScheduledAtAttribute($value)
    {
        $this->attributes['scheduled_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
