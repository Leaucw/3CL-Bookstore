<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Pipes;

use Closure;

class FormatMessage
{
    public function handle($payload, Closure $next)
    {
        $payload['message'] = trim($payload['message']);
        if (!isset($payload['title'])) {
            $payload['title'] = 'Announcement';
        }
        return $next($payload);
    }
}
