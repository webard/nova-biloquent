<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Laravel\Nova\Http\Requests\NovaRequest;

class ReportDatasetFilters
{
    /**
     * @return array<mixed>
     */
    public function filters(NovaRequest $request, string $datasetResource)
    {
        $dd = new $datasetResource();

        $fields = $dd->availableFilters($request);

        return $fields->toArray();
    }
}
