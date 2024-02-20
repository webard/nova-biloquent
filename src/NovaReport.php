<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\TrashedStatus;
use Webard\Biloquent\Report;

/**
 * @template TModel of \Webard\Biloquent\Report
 *
 * @mixin TModel
 *
 * @method mixed getKey()
 */
abstract class NovaReport extends Resource
{
    /**
     * @var int[]
     */
    public static $perPageOptions = [100];

    /**
     * @var array<mixed>
     */
    public $datasetSelects = [];

    /**
     * @var string
     */
    public static $clickAction = 'ignore';

    /**
     * Undocumented function
     *
     * @param  Builder<Report>  $query
     * @return  Builder<Report>
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query;
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function authorizedToView(Request $request): bool
    {
        return false;
    }

    /**
     * @var string
     */
    public static $tableStyle = 'default';

    /**
     * @var bool
     */
    public static $showColumnBorders = true;

    public static function searchable(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    abstract public function reportFields(NovaRequest $request): array;

    /**
     * Get the filters that are available for the given request.
     *
     * @return \Illuminate\Support\Collection<int, \Laravel\Nova\Filters\Filter>
     */
    public function availableFilters(NovaRequest $request)
    {
        return $this->resolveFiltersForReport($request)
            ->concat($this->resolveFiltersForDataset($request))
            ->filter->authorizedToSee($request)
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Laravel\Nova\Filters\Filter>
     */
    public function resolveFiltersForDataset(NovaRequest $request)
    {
        $filters = array_values($this->filter($this->filters($request)));

        $reportFilters = [];
        $datasetFilters = [];

        $datasetSelects = [];

        foreach ($filters as $key => $filter) {
            if ($filter instanceof ReportFields || $filter instanceof ReportGrouping) {
                //$reportFilters[] = $filter;
            } else {
                $datasetFilters[] = $filter;
                // if (isset($filter->field->belongsToRelationship)) {
                //     assert($filter->field instanceof BelongsTo);
                //     // TODO: fetch from relation, not hardcoded
                //     $this->datasetSelects[] = $filter->field->attribute.'_id';
                // } else {
                //     $this->datasetSelects[] = $filter->field->attribute;
                // }

            }

        }

        return collect($datasetFilters);
    }

    /**
     * Get the filters that are available for Report Model
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function resolveFiltersForReport(NovaRequest $request)
    {
        $filters = array_values($this->filter($this->filters($request)));

        $reportFilters = [];

        foreach ($filters as $key => $filter) {
            if ($filter instanceof Filter && ($filter instanceof ReportFields || $filter instanceof ReportGrouping)) {
                $reportFilters[] = $filter;
            }

        }

        return new Collection($reportFilters);
    }

    /**
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {

        $fields = [];
        $filters = [];

        $filters = $this->filters($request);
        $filtersString = $request->get('filters');
        if ($filtersString !== null) {
            $filters = json_decode(base64_decode($filtersString), true);
        }



        foreach ($filters as $k => $filter) {
            foreach ($filter as $class => $options) {
                if ($class === ReportGrouping::class || $class === ReportFields::class) {
                    $fields = [...$fields, ...(new Collection($options))->filter(fn ($field) => $field === true)->toArray()];
                }

            }
        }

        $return = [];

        foreach ($this->reportFields($request) as $field) {
            $withId = $field->attribute.'_id';
            if (isset($fields[$field->attribute])) {
                $return[] = $field;
            }

            if (isset($fields[$withId])) {
                $return[] = $field;
            }
        }

        return $return;
    }

    /**
     * @return array<mixed>
     */
    public function filters(NovaRequest $request)
    {
        assert($this->resource instanceof Report);

        $groups = (new Collection($this->resource->groups()))->mapWithKeys(fn ($group, $key) => [Str::of($key)->replace('_id', '')->replace('_', ' ')->title()->__toString() => $key])->toArray();

        $aggregators = (new Collection($this->resource->aggregators()))->mapWithKeys(fn ($group, $key) => [Str::of($key)->replace('_id', '')->replace('_', ' ')->title()->__toString() => $key])->toArray();

        // @phpstan-ignore-next-line
        $filters = (new ReportDatasetFilters())->filters($request, static::$datasetResource);

        return [
            new ReportGrouping(
                grouping: $groups
            ),
            new ReportFields(
                grouping: $aggregators
            ),
            ...$filters,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Report>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Report>
     */
    public static function buildIndexQuery(NovaRequest $request, $query, $search = null,
        array $filters = [], array $orderings = [],
        $withTrashed = TrashedStatus::DEFAULT)
    {

        $reportFilters = [];
        $datasetFilters = [];

        foreach ($filters as $key => $filter) {
            // ReportField and ReportGrouping are filters that are applied to the report Model
            if ($filter->filter instanceof ReportFields || $filter->filter instanceof ReportGrouping) {
                $reportFilters[] = $filter;
            } else {
                // Other filters are applied to the dataset
                $datasetFilters[] = $filter;
            }

        }

         $query = $query->enhance(function ($query) use ($datasetFilters, $request, $search, $withTrashed, $orderings) {


            foreach ($datasetFilters as $filter) {

                if (isset($filter->filter?->field->belongsToRelationship)) {
                    assert($filter->filter->field instanceof BelongsTo);
                    // TODO: fetch from relation, not hardcoded
                    $query = $query->addSelect($filter->filter->field->attribute.'_id');
                } else {
                    $query = $query->addSelect($filter->filter->field->attribute);
                }
            }

            $query = static::initializeQuery($request, $query, (string) $search, $withTrashed);
            return static::applyFilters($request, $query, $datasetFilters);
        });

        $query =  static::applyOrderings(static::applyFilters(
            $request, $query, $reportFilters
        ), $orderings)->tap(function ($query) use ($request): void {
            static::indexQuery($request, $query->with(static::$with));
        });

        return $query->prepare();

    }
}
