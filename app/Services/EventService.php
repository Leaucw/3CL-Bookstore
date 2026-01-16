<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function schedule(int $eventId): void {
        DB::transaction(function () use ($eventId) {
            $e = Event::lockForUpdate()->findOrFail($eventId);
            if (!in_array($e->status, [Event::DRAFT, Event::SCHEDULED])) return;
            $e->update(['status'=>Event::SCHEDULED]);
        });
    }

    public function goLive(int $eventId): void {
        DB::transaction(function () use ($eventId) {
            $e = Event::lockForUpdate()->findOrFail($eventId);
            if ($e->status !== Event::SCHEDULED) return;
            $e->update(['status'=>Event::LIVE]);
        });
    }

    public function cancel(int $eventId, ?string $reason=null): void {
        DB::transaction(function () use ($eventId, $reason) {
            $e = Event::lockForUpdate()->findOrFail($eventId);
            if (in_array($e->status, [Event::COMPLETED, Event::CANCELLED])) return;
            $e->update(['status'=>Event::CANCELLED,'cancellation_reason'=>$reason]);
        });
    }
}
