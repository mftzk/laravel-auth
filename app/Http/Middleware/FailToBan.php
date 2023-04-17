<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\CacheManager;

class FailToBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function handle($request, Closure $next, $maxAttempts = 3, $decaySeconds = 30)
    {
        $key = $this->resolveRequestSignature($request);
        $cache = $this->cache->store();

        if ($cache->has($key . ':fail2ban')) {
            abort(429, 'Too many failed login attempts. Please try again later.');
        }

        $maxAttempts = intval($maxAttempts);
        $decaySeconds = intval($decaySeconds);

        $response = $next($request);

        if ($response->getStatusCode() == 401) {
            $this->incrementAttempts($cache, $key, $maxAttempts, $decaySeconds);
        }

        return $response;
    }

    protected function incrementAttempts($cache, $key, $maxAttempts, $decaySeconds)
    {
        $attempts = $cache->get($key . ':fail2ban:attempts', 0) + 1;

        if ($attempts >= $maxAttempts) {
            $cache->put($key . ':fail2ban', true, $decaySeconds);
            $cache->put($key . ':fail2ban:attempts', 0, $decaySeconds);
        } else {
            $cache->put($key . ':fail2ban:attempts', $attempts, $decaySeconds);
        }
    }

    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }

    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }
}
