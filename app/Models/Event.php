<?php

namespace App\Models;

use App\Observers\EventObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([EventObserver::class])]

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    use HasSpatial;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'location' => Point::class,
    ];
    /**
     * The users that belong to the event.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user');
    }
}
