<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Webard\Biloquent\Report;

class ReportSummary extends BooleanFilter
{
    /**
     * @param  array<string,mixed>  $fields
     */
    public function __construct(
        public array $fields = []
    ) {
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Report>  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder<Report>
     */
    public function apply(NovaRequest $request, $query, $value)
    {

        $grouping = (new Collection($value))->filter(fn ($v) => $v === true)->keys();

        if ($grouping->isEmpty()) {
            return $query;
        }

        //@phpstan-ignore-next-line
        return $query->columns($grouping->toArray());
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,mixed>
     */
    public function options(NovaRequest $request)
    {
        $fields = (new Collection($this->fields))
            ->mapWithKeys(fn ($group, $key) => [$this->prepareTitle($key) => $key])
            ->toArray();

        return $fields;
    }

    /**
     * Prepare title for the filter
     */
    private function prepareTitle(string $key): string
    {
        return (string) Str::of($key)->replace('_id', '')->headline();
    }
}
