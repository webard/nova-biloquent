<?php

namespace Webard\NovaBiloquent\Traits;

use Illuminate\Http\Request;

/**
 * Reports are read-only
 */
trait DisablesWriting
{
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
}
