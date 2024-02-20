<?php

namespace Webard\NovaBiloquent\Traits;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\TrashedStatus;
use Webard\Biloquent\Report;
use Webard\NovaBiloquent\ReportGrouping;
use Webard\NovaBiloquent\ReportSummary;

trait PerformsQueriesForDatasetAndReport
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function reportQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function datasetQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Report>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Report>
     *
     * @override
     */
    public static function buildIndexQuery(NovaRequest $request, $query, $search = null,
        array $filters = [], array $orderings = [],
        $withTrashed = TrashedStatus::DEFAULT)
    {
        [$reportFilters, $datasetFilters] = static::splitFiltersForDatasetAndReport($filters);

        // Enhance dataset query with filters that can be applied to the dataset
        $query = $query->enhance(function ($query) use ($datasetFilters, $request, $search, $withTrashed) {

            foreach ($datasetFilters as $filter) {

                // TODO: explain this code, probably is needed to properly fetch data from dataset via report
                if (isset($filter->filter?->field->belongsToRelationship)) {
                    assert($filter->filter->field instanceof BelongsTo);
                    // TODO: fetch from relation, not hardcoded
                    $query = $query->addSelect($filter->filter->field->attribute.'_id');
                } else {
                    $query = $query->addSelect($filter->filter->field->attribute);
                }
            }

            // Enable searching and withTrashed on dataset query
            $query = static::initializeQuery($request, $query, (string) $search, $withTrashed);

            // Apply filters to dataset query
            // Ordering is not applied intentionally, as it is applied only to the report query
            return static::applyFilters($request, $query, $datasetFilters)->tap(function ($query) use ($request): void {
                static::datasetQuery($request, $query);
            });
        });

        /// initializeQuery is not called here, as it is called in the datasetQuery to enable searching and withTrashed
        $query = static::applyOrderings(static::applyFilters(
            $request, $query, $reportFilters
        ), $orderings)->tap(function ($query) use ($request): void {
            static::reportQuery($request, $query);
        });

        // This method is necessary to run report
        // @phpstan-ignore-next-line method is available in Webard\Biloquent\Report
        return $query->prepare();

    }

    protected static function splitFiltersForDatasetAndReport($filters)
    {
        $reportFilters = [];
        $datasetFilters = [];

        foreach ($filters as $filter) {
            // ReportField and ReportGrouping are filters that are applied to the report Model
            if ($filter->filter instanceof ReportSummary || $filter->filter instanceof ReportGrouping) {
                $reportFilters[] = $filter;
            } else {
                // Other filters are applied to the dataset
                $datasetFilters[] = $filter;
            }
        }

        return [$reportFilters, $datasetFilters];
    }
}
