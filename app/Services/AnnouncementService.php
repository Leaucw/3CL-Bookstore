<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Services;

use App\Models\Announcement;
use Illuminate\Pipeline\Pipeline;

class AnnouncementService
{
    public function publish(int $announcementId, array $payload): void
    {
        $announce = Announcement::findOrFail($announcementId);
        $payload['meta'] = array_merge($payload['meta'] ?? [], ['announcement_id' => $announcementId]);
        $obj = (object) $payload;

        try {
            app(Pipeline::class)
                ->send($obj)
                ->through([
                    \App\Pipes\EnsureAudience::class,
                    \App\Pipes\FormatMessage::class,
                    \App\Pipes\DispatchEmail::class,
                    \App\Pipes\DispatchSms::class,
                    \App\Pipes\DispatchPush::class,
                ])
                ->then(function ($out) use ($announce) {
                    $count = is_countable($out->recipients) ? count($out->recipients)
                          : (method_exists($out->recipients,'count') ? $out->recipients->count() : 0);
                    $announce->update(['status'=>'sent','published_at'=>now(),'send_count'=>$count]);
                });
        } catch (\Throwable $e) {
            $announce->update(['status'=>'failed']);
            report($e);
        }
    }
}
