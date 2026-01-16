<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $eventId;
    protected string $reason;

    public function __construct(int $eventId, string $reason = 'Cancelled')
    {
        $this->eventId = $eventId;
        $this->reason = $reason;
    }

    public function handle(): void
    {
        $event = Event::find($this->eventId);
        if ($event) {
            $event->update(['status' => Event::CANCELLED]);
            // Could log reason or notify users
        }
    }
}
