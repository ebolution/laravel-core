<?php

namespace Ebolution\Core\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IngestRouteParameters
{
    /**
     * Ingest all but these keys on the route
     */
    private const DO_NOT_INGEST_KEYS = ['id'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ingested = [];
        $not_ingested = [];
        $parameters = $request->route()->parameters();
        foreach($parameters as $key => $value) {
            if (in_array($key, self::DO_NOT_INGEST_KEYS)) {
                $not_ingested[$key] = $value;
            } else {
                $ingested[$key] = $value;
            }
        }
        $request->merge($ingested);
        $request->route()->parameters = $not_ingested;

        return $next($request);
    }
}
