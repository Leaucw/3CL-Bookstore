<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'event_id'      => $this->event_id,
            'user_id'       => $this->user_id,
            'status'        => $this->status,
            'awarded_points'=> $this->awarded_points,
            'awarded_at'    => optional($this->awarded_at)->toIso8601String(),
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}
