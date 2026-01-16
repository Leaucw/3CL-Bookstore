<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Pipes;

use Closure;

class DispatchChannels
{
    public function handle($payload, Closure $next)
    {
        foreach ($payload['channels'] as $channel) {
            switch ($channel) {
                case 'mail':
                    // dispatch email job / notification
                    break;
                case 'sms':
                    // dispatch sms job
                    break;
                case 'push':
                    // dispatch push notification job
                    break;
            }
        }
        return $next($payload);
    }
}
