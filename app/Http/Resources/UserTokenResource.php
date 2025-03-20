<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTokenResource extends JsonResource
{
    private $additionalData;

    public function __construct($resource, $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge([
            new UserResource($this,[
            'token' => $this->createToken('auth-token')->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60])

        ], $this->additionalData);
    }
}
