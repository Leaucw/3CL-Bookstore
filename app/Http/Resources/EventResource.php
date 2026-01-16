<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type,
            'delivery'    => $this->delivery_mode,
            'visibility'  => $this->visibility,
            'status'      => $this->status,
            'points'      => (int) $this->points_reward,
            'starts_at'   => optional($this->starts_at)->toIso8601String(),
            'ends_at'     => optional($this->ends_at)->toIso8601String(),
            'organizer'   => $this->organizer?->only(['id','name','email']),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
