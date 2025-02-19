<?php

namespace App\Nova;

use App\Nova\Actions\UserJoinEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

use Mostafaznv\NovaMapField\Fields\MapPointField;

class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Event>
     */
    public static $model = \App\Models\Event::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name'
    ];


    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        if (!$request->user()->is_admin) {
            return $query->where('status', 'live');
        }
        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable()->creationRules(['required']),
            Date::make('Start Date', 'start_date')->sortable()->creationRules(['required']),
            Date::make('End Date', 'end_date')->sortable()->creationRules(['required']),
            Number::make('Duration', 'days')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make('Start Time', 'start_time')
                ->withMeta(['extraAttributes' => ['type' => 'time']])->creationRules(['required']),
            Text::make('End Time', 'end_time')
                ->withMeta(['extraAttributes' => ['type' => 'time']])->creationRules(['required']),
            Number::make('Capacity', 'capacity')->creationRules(['required']),
            Number::make('Wait list capacity', 'wait_list_capacity')->creationRules(['required']),
            Badge::make('Status', '', function () {
                return $this->getUserJoinStatus(auth()->id());
            })->map([
                0 => 'info',
                1 => 'warning',
                2 => 'success',
            ])->labels([
                0 => 'Not Joined',
                1 => 'On Waiting List',
                2 => 'Joined',
            ]),
            Select::make('Status', 'status')->options(
                [
                    'live' => 'Live',
                    'draft' => 'Draft'
                ]
            )->displayUsingLabels()->hideFromIndex()->hideFromDetail()->creationRules(['required']),
            Badge::make('Status', 'status')->map([
                'live' => 'success',
                'draft' => 'warning'
            ])->labels(
                [
                    'live' => 'Live',
                    'draft' => 'Draft'
                ]
            ),
            MapPointField::make('location')->hideFromIndex()->creationRules(['required']),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            UserJoinEvent::make()
                ->showOnIndex()
                ->showInline()
                ->confirmText('Are you sure you want to join this event?')
                ->confirmButtonText('Join')
                ->cancelButtonText("Don't join"),
        ];
    }
}
