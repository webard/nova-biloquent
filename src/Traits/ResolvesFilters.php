<?php

namespace Webard\NovaBiloquent\Traits;

use Illuminate\Support\Collection;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Webard\Biloquent\Report;
use Webard\NovaBiloquent\ReportDatasetFilters;
use Webard\NovaBiloquent\ReportGrouping;
use Webard\NovaBiloquent\ReportSummary;

trait ResolvesFilters
{
    /**
     * Get the filters available on the entity.
     *
     * @return array<mixed>
     *
     * @override
     */
    public function filters(NovaRequest $request)
    {
        return [
            $this->groupingFilterForReport($request),
            $this->summaryFilterForReport($request),
            ...$this->filtersForDataset($request),
        ];
    }

    /**
     * Get aggregate filter that is available for Report models
     */
    public function summaryFilterForReport(NovaRequest $request): ReportSummary
    {
        assert($this->resource instanceof Report);

        return new ReportSummary(
            fields: $this->resource->aggregators()
        );
    }

    /**
     * Get grouping filter that is available for Report models
     */
    public function groupingFilterForReport(NovaRequest $request): ReportGrouping
    {
        assert($this->resource instanceof Report);

        return new ReportGrouping(
            fields: $this->resource->groups()
        );
    }

    /**
     * Get the filters that are available for Dataset model
     *
     * @return array<mixed>
     */
    public function filtersForDataset(NovaRequest $request): array
    {
        return (new ReportDatasetFilters())->filters($request, static::datasetResource());
    }

    /**
     * Get the filters that are available for the given request.
     *
     * @return \Illuminate\Support\Collection<int, \Laravel\Nova\Filters\Filter>
     *
     * @override
     */
    public function availableFilters(NovaRequest $request): Collection
    {
        return $this->resolveFiltersForReport($request)
            ->concat($this->resolveFiltersForDataset($request))
            ->filter->authorizedToSee($request)
            ->values();
    }

    /**
     * Get the filters that are applicable for for Dataset model
     *
     * @return \Illuminate\Support\Collection<int, \Laravel\Nova\Filters\Filter>
     */
    public function resolveFiltersForDataset(NovaRequest $request): Collection
    {
        $filters = array_values($this->filter($this->filters($request)));

        $datasetFilters = [];
        foreach ($filters as $filter) {
            if (! $filter instanceof ReportSummary && ! $filter instanceof ReportGrouping) {
                $datasetFilters[] = $filter;
            }
        }

        return new Collection($datasetFilters);
    }

    /**
     * Get the filters that are available for Report model
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function resolveFiltersForReport(NovaRequest $request): Collection
    {
        $filters = array_values($this->filter($this->filters($request)));

        $reportFilters = [];

        foreach ($filters as $filter) {
            if ($filter instanceof Filter && ($filter instanceof ReportSummary || $filter instanceof ReportGrouping)) {
                $reportFilters[] = $filter;
            }

        }

        return new Collection($reportFilters);
    }
}
