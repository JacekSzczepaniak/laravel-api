<?php

namespace Modules\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJson
{
    public function handle(Request $request, Closure $next): mixed
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
