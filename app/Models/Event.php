<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    use HasSpatial;

    // Ensure 'scheduled_at' is cast to a DateTime object
    protected $casts = [
        'scheduled_at' => 'datetime',
        'location' => Point::class,
    ];

    // Mutator to format 'scheduled_at' before saving
    public function setScheduledAtAttribute($value)
    {
        $this->attributes['scheduled_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }


    /**
     * The users that belong to the event.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user');
    }
}
