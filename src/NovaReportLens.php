<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\NovaRequest;
use Webard\NovaBiloquent\Traits\ResolvesFilters;

abstract class NovaReportLens extends Lens
{
    use ResolvesFilters;

    public static string $parentResource = '';

    /**
     * @return array<mixed>
     */
    public static function searchableColumns(): array
    {
        return (static::$parentResource)::$search ?? [];
    }

    protected function parentResource(NovaRequest $request): NovaReport
    {
        return $request->newResourceWith( new static::$parentResource::$model);
    }

    public static function query(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Get the fields available to the lens.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request)
    {
        $this->parentResource($request)->fields($request);
    }

    /**
     * Get the cards available on the lens.
     *
     * @return array<string>
     */
    public function cards(NovaRequest $request)
    {
        return $this->parentResource($request)->cards($request);
    }

    /**
     * Get the filters available for the lens.
     *
     * @return array<string>
     */
    public function filters(NovaRequest $request)
    {
        return $this->parentResource($request)->filters($request);
    }
}
