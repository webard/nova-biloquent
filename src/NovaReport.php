<?php

declare(strict_types=1);

namespace Webard\NovaBiloquent;

use Laravel\Nova\Resource;
use Webard\NovaBiloquent\Traits\DisablesWriting;
use Webard\NovaBiloquent\Traits\PerformsQueriesForDatasetAndReport;
use Webard\NovaBiloquent\Traits\ResolvesFields;
use Webard\NovaBiloquent\Traits\ResolvesFilters;

/**
 * @template TModel of \Webard\Biloquent\Report
 *
 * @mixin TModel
 *
 * @method mixed getKey()
 */
abstract class NovaReport extends Resource
{
    use DisablesWriting;
    use PerformsQueriesForDatasetAndReport;
    use ResolvesFields;
    use ResolvesFilters;

    public static string $datasetResource;

    /**
     * @var int[]
     */
    public static $perPageOptions = [100];

    /**
     * @var string
     */
    public static $clickAction = 'ignore';

    /**
     * @var string
     */
    public static $tableStyle = 'default';

    /**
     * @var bool
     */
    public static $showColumnBorders = true;

    public static function datasetResource(): string
    {
        return static::$datasetResource;
    }
}
