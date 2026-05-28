<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getPermissionNames(),
            'created_at' => $this->created_at,
        ];
    }
}
