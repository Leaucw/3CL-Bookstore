<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Pipes;

use Closure;

class EnsureAudience
{
    public function handle($payload, Closure $next)
    {
        if (empty($payload['recipients'])) {
            // fallback: no recipients = skip sending
            return $payload;
        }
        return $next($payload);
    }
}
