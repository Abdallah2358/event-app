<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Carbon;

class EventObserver
{

    /**
     * Handle the Event "saving" event.
     */
    public function saving(Event $event)
    {
        if (!empty($event->start_date) && !empty($event->end_date)) {
            $event->days = Carbon::parse($event->start_date)
                ->diffInDays(Carbon::parse($event->end_date)) + 1;
        }
    }

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
