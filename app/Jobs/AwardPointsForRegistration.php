<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Jobs;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AwardPointsForRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $registrationId;

    public function __construct(int $registrationId)
    {
        $this->registrationId = $registrationId;
    }

    public function handle(): void
    {
        try {
            $reg = EventRegistration::find($this->registrationId);
            if ($reg && $reg->user && $reg->event) {
                $user   = $reg->user;
                $points = $reg->event->points_reward ?? 0;

                if ($points > 0 && !$reg->awarded_points) {
                    $success = false;

                    // 🔹 Try EXTERNAL API first
                    try {
                        $base = rtrim(config('services.users_api.base'), '/');
                        $timeout = (float) config('services.users_api.timeout', 5);

                        $res = Http::timeout($timeout)
                            ->acceptJson()
                            ->post("$base/users/{$user->id}/points/add", [
                                'points' => $points,
                            ]);

                        if ($res->ok()) {
                            $success = true;
                        }
                    } catch (\Throwable $e) {
                        Log::warning("External API award failed", [
                            'registration_id' => $this->registrationId,
                            'user_id'         => $user->id,
                            'points'          => $points,
                            'error'           => $e->getMessage(),
                        ]);
                    }

                    // 🔹 If EXTERNAL fails → INTERNAL API
                    if (!$success) {
                        try {
                            $internalBase = url('/api/v1');
                            $res = Http::acceptJson()
                                ->post("$internalBase/users/{$user->id}/points/add", [
                                    'points' => $points,
                                ]);

                            if ($res->ok()) {
                                $success = true;
                            }
                        } catch (\Throwable $e) {
                            Log::error("Internal API award failed", [
                                'registration_id' => $this->registrationId,
                                'user_id'         => $user->id,
                                'points'          => $points,
                                'error'           => $e->getMessage(),
                            ]);
                        }
                    }

                    // 🔹 Mark registration as awarded regardless of method
                    $reg->update([
                        'awarded_points' => $points,
                        'awarded_at'     => now(),
                    ]);

                    // 🔹 Audit log
                    Log::info("Audit: Points awarded for registration", [
                        'registration_id' => $this->registrationId,
                        'user_id'         => $user->id,
                        'points'          => $points,
                        'method'          => $success ? 'API' : 'fallback',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("AwardPointsForRegistration job failed", [
                'registration_id' => $this->registrationId,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);
        }
    }
}
