<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $announcementId;
    protected array $payload;

    public function __construct(int $announcementId, array $payload)
    {
        $this->announcementId = $announcementId;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $announcement = Announcement::find($this->announcementId);
        if (!$announcement) return;

        // Chain of Responsibility → Pipes
        app(Pipeline::class)
            ->send($this->payload)
            ->through([
                \App\Pipes\EnsureAudience::class,
                \App\Pipes\FormatMessage::class,
                \App\Pipes\DispatchChannels::class,
            ])
            ->then(function ($processed) use ($announcement) {
                $announcement->update(['status' => Announcement::SENT]);
            });
    }
}
