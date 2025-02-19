<?php

namespace App\Nova\Actions;

use App\Mail\JoinEventConfirmation;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Notifications\NovaNotification;

class UserJoinEvent extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Join Event';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $events)
    {

        foreach ($events as $event) {

            if ($event->status == 'live') {
                $user = request()->user();
                $event_exists  = $event_exists = $user->events()->find($event->id) !== null;

                if ($event_exists) { // user already joined this event
                    $user->notify(
                        NovaNotification::make()->message('You already joined ' . $event->name . '.')
                            ->type('warning')
                            ->icon('information-circle')
                    );
                    return ActionResponse::danger('You already joined ' . $event->name . '.');
                }

                if (!$this->overlapsWithOtherEvents($user, $event)) {
                    if ($event->capacity) {

                        $event->users()->attach($user);
                        $event->capacity -= 1;
                        $event->save();
                        $user->notify(
                            NovaNotification::make()->message('Event ' . $event->name . ' Joined Successfully.')
                                ->type('success')
                                ->icon('check')
                        );
                        Mail::to($user->email)->send(new JoinEventConfirmation(event: $event, user: $user));
                        return ActionResponse::message('Event ' . $event->name . ' Joined Successfully.');
                    } else if ($event->wait_list_capacity) { // event at full capacity but wait list has capacity

                        $event->users()->attach($user, ['is_on_wait_list' => true]);
                        $event->wait_list_capacity -= 1;
                        $event->save();
                        $user->notify(
                            NovaNotification::make()->message('You have been added to wait list for event ' . $event->name . ' because it is at full capacity.')
                                ->type('warning')
                                ->icon('clock')
                        );
                        return ActionResponse::message('Event ' . $event->name . 'is full you were added to wait list Successfully.');

                        # Todo : add Wait list Email
                        // Mail::to($user->email)->send(new JoinEventConfirmation(event: $event, user: $user));

                    } else { // event at full capacity and at full wait list capacity

                        NovaNotification::make()->message('Sorry event ' . $event->name . ' is at full capacity.')
                            ->type('error')
                            ->icon('ban');
                        return ActionResponse::danger('Sorry event ' . $event->name . ' is at full capacity.');
                    }
                } else { // event overlaps with other events user joined

                    NovaNotification::make()->message('Sorry the event ' . $event->name . ' overlaps with other events you joined.')
                        ->type('error')
                        ->icon('ban');
                    return ActionResponse::danger('Sorry the event ' . $event->name . ' overlaps with other events you joined.');
                }
            }
            return ActionResponse::danger('Can only join Live Events');
        }
        return $events;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
    protected function overlapsWithOtherEvents(User $user, Event $event): bool
    {
        $events_overlapping_before = $user->events()
            ->whereDate('end_date', '>=', $event->start_date)
            ->whereDate('start_date', '<=', $event->start_date)
            ->where('end_time', '>', $event->start_time)
            ->get();

        $events_overlapping_after = $user->events()
            ->whereDate('start_date', '<=', $event->end_date)
            ->whereDate('end_date', '>=', $event->end_date)
            ->where('end_time', '>', $event->start_time)
            ->get();
        $events_overlapping_I_surround = $user->events()
            ->whereDate('start_date', '>=', $event->start_date)
            ->whereDate('end_date', '<=', $event->end_date)
            ->where('end_time', '>=', $event->start_time)
            ->get();
        $events_overlapping_surrounded = $user->events()
            ->whereDate('start_date', '>=', $event->start_date)
            ->whereDate('end_date', '<', $event->start_date)
            ->where('end_time', '>', $event->start_time)
            ->get();
        $events_overlapping_surround_me = $user->events()
            ->whereDate('start_date', '<=', $event->start_date)
            ->whereDate('end_date', '>=', $event->end_date)
            ->where('end_time', '>=', $event->start_time)
            ->get();


        return (count($events_overlapping_before) > 0
            || count($events_overlapping_after) > 0
            || count($events_overlapping_I_surround) > 0
            || count($events_overlapping_surround_me) > 0
        );
    }
}
