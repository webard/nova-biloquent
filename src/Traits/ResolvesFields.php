<?php

namespace Webard\NovaBiloquent\Traits;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Webard\NovaBiloquent\ReportGrouping;
use Webard\NovaBiloquent\ReportSummary;

trait ResolvesFields
{
    /**
     * In this method you need to define the fields that should be displayed in the report.
     *
     * @return array<mixed>
     */
    abstract public function reportFields(NovaRequest $request): array;

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
                if ($class === ReportGrouping::class || $class === ReportSummary::class) {
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
}
