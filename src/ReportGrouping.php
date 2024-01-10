<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Illuminate\Support\Collection;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Webard\Biloquent\Report;

class ReportGrouping extends BooleanFilter
{
    /**
     * @param  array<string,mixed>  $grouping
     */
    public function __construct(
        public array $grouping = []
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

        $query = $query->getModel();

        //@phpstan-ignore-next-line
        return $query->grouping($grouping->toArray());
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,mixed>
     */
    public function options(NovaRequest $request)
    {
        return $this->grouping;
    }
}
