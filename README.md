# NOVA BILOQUENT

Reports for Eloquent models, displayed as Nova resource.

See http://github.com/webard/biloquent for more info.

**Package is under development and in very early stage.**


## Define Resource

Sample base on Order model with total and created_at fields required.

Create file `App\Nova\OrderReport.php`:

```php

declare(strict_types=1);

namespace App\Nova;

use Webard\NovaBiloquent\NovaReport;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\User;

class OrderReport extends NovaReport
{
    public static $model = \App\Reports\OrderReport::class;

    public static string $datasetResource = \App\Nova\Order::class;

    public function reportFields(NovaRequest $request): array
    {
        return [
            Text::make('Year', 'year')->sortable(),
            Text::make('Month', 'month')->sortable(),
            Text::make('Day', 'day')->sortable(),
            Text::make('Date', 'date')->sortable(),
            BelongsTo::make('Customer', 'customer', User::class)->sortable(),
            Number::make('Total orders', 'total_orders')->sortable()->filterable(),
            Number::make('Avg. amount', 'average_amount')->sortable()->filterable(), 
        ];
    }
}
