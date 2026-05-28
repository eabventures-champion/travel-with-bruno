<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'bio' => $this->bio,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'avatar_url' => $this->avatar_url,
            'preferences' => $this->preferences,
        ];
    }
}
