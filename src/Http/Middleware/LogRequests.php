<?php

namespace Webmonet\ElkLogger\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    public static float $startTime = 0.0;

    public function handle(Request $request, Closure $next)
    {
        static::$startTime = microtime(true);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $channel = config('elk-logger.channel_name', 'elasticsearch');
        $durationHeader = config('elk-logger.request_logging.duration_header', 'Duration');
        $message = config('elk-logger.request_logging.message', 'Incoming Request');

        Log::channel($channel)->info($message, [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'body' => $request->all(),
            'duration' => round((microtime(true) - static::$startTime) * 1000, 2),
            'db-duration' => (float) $response->headers->get($durationHeader, 0),
            'response' => (string) $response,
        ]);
    }
}
